<?php
namespace PowerMocker;

use Composer\Autoload\ClassLoader;
use PowerMocker\Filter\Autoload;
use PowerMocker\Filter\ImportMyAutoload;

/**
 * 自动加载管理器
 * Class AutoloadManager
 * @package PowerMocker
 */
class AutoloadManager
{
    /**
     * 单例
     * @var $this
     */
    private static $instance;

    /**
     * composerLoader 实例
     * @var ComposerAutoload
     */
    private static $composerLoader = null;

    /**
     * Init constructor.
     */
    protected function __construct()
    {
        $this->registerFilter();
    }

    /**
     * 注册过滤器
     */
    private function registerFilter()
    {
        stream_filter_register(Autoload::NAME, Autoload::class);
        stream_filter_register(ImportMyAutoload::NAME, ImportMyAutoload::class);
    }

    /**
     * 单例入口
     * @return AutoloadManager
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置composer模式
     * @param $autoloadFile
     * @return ComposerAutoload|null
     * @throws \Exception
     */
    public function includeComposerAutoload($autoloadFile)
    {
        if (null !== self::$composerLoader) {
            //composer加载模式已经设置，无须再次设置
            throw new PowerMockException("composer is already set");
        }
        include $autoloadFile;

        $dir = dirname($autoloadFile).'/composer';

        self::$composerLoader = $loader = new ComposerAutoload();

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if (0 && $useStaticLoader) {
            //TODO:1.重新查找已加载类：ComposerAutoloaderInit38e3c43a0dd28ad519cb9ef4005306eb
            //TODO:2.重新闭包设置
            //获取已经加载的类名
//            $composerAutoloaderInit = $this->getComposerAutoloaderInitName();
//            $className = "{$composerAutoloaderInit}";
//            $closureLoader = \Closure::bind(function () use ($loader, $className) {
//                $loader->prefixLengthsPsr4 = $className::$prefixLengthsPsr4;
//                $loader->prefixDirsPsr4 = $className::$prefixDirsPsr4;
//                $loader->prefixesPsr0 = $className::$prefixesPsr0;
//                $loader->classMap = $className::$classMap;
//            }, null, ComposerAutoload::class);
//            call_user_func($closureLoader);
        } else {
            $map = require $dir . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require $dir . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require $dir . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        //将其放入第一个位置
        $functions = spl_autoload_functions();
        foreach ($functions as $v) {
            spl_autoload_unregister($v);
        }

        $loader->register(true);

        //重新register上
        foreach ($functions as $v) {
            spl_autoload_register($v);
        }

        return $loader;
    }

    /**
     * 获取ComposerAutoloaderInit类名称
     * @return mixed
     * @throws \Exception
     */
    private function getComposerAutoloaderInitName()
    {
        $declaredClasses = get_declared_classes();
        $declaredClasses = array_reverse($declaredClasses); #反转下找到真正的ComposerAutoloaderInit，有些IDE PHPUNIT会自动最先引入
        foreach ($declaredClasses as $v) {
            if (strpos($v, 'ComposerAutoloaderInit') === 0) {
                return $v;
            }
        }
        throw new \Exception('未找到ComposerAutoloaderInit，请确保你已引入了composer的autoload.php');
    }

    /**
     * 引入自定义加载器
     * @param $file
     */
    public function importMyAutoload($file)
    {
        require "php://filter/read=".ImportMyAutoload::NAME."/resource={$file}";
    }
}
