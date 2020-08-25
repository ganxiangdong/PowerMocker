<?php
namespace PowerMocker;

/**
 * Class Mock
 * @package PowerMocker
 */
class Mock
{
    /**
     * 单例
     * @var self
     */
    private static $instance = null;

    /**
     * 单例入口
     * @return $this
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * mock一个类方法
     * @param string $classNamespace 要mock的类名(包含命名空间)
     * @param string $methodName 要mock的方法名
     * @param mixed $returnVal mock后返回的值
     */
    public function method($classNamespace, $methodName, $returnVal)
    {
        $identify = $this->getMethodIdentify($classNamespace, $methodName);
        Proxy::instance()->register($identify, $returnVal);
    }

    /**
     * 清除某方法的Mock
     * @param $classNamespace
     * @param $methodName
     */
    public function clearMethod($classNamespace, $methodName)
    {
        $identify = $this->getMethodIdentify($classNamespace, $methodName);
        Proxy::instance()->clear($identify);
    }

    /**
     * 获取方法的唯一标识符
     * @param $classNamespace
     * @param $methodName
     * @return string
     */
    private function getMethodIdentify($classNamespace, $methodName)
    {
        $classNamespace = ltrim($classNamespace, '\\');
        $identify = "{$classNamespace}::{$methodName}";
        return $identify;
    }

    /**
     * 清除所有mock
     */
    public function clearAll()
    {
        Proxy::instance()->clearAll();
    }

    /**
     * 暂停mock
     */
    public function pause()
    {
        Proxy::instance()->pause();
    }

    /**
     * 暂停后重新开始
     */
    public function restart()
    {
        Proxy::instance()->restart();
    }
}
