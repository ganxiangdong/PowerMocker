<?php
namespace PowerMocker;

/**
 * 调用代理注册与调用管理器
 * Class Proxy
 * @package PowerMocker
 */
class Proxy
{
    /**
     * 单例
     * @var $this
     */
    private static $instance = null;

    /**
     * 调用映射
     * @var array
     */
    private static $callMap = [];

    /**
     * 调用映射备份
     * @var array
     */
    private static $callMapBack = [];

    /**
     * 单例入口
     * @return self
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 注册
     * @param $identify string 唯一标识符号
     * @param $returnValue mixed
     */
    public function register($identify, $returnValue)
    {
        self::$callMap[$identify] = $returnValue;
    }

    /**
     * 拦截调用回来的方法
     * @param $identify
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function callByMock($identify, $args)
    {
        if (isset(self::$callMap[$identify])) {
            if (is_callable(self::$callMap[$identify])) {
                return call_user_func_array(self::$callMap[$identify], $args);
            } else {
                return self::$callMap[$identify];
            }
        }
        //返回异常，使拦截不生效
        throw new PowerMockException('not register mock proxy');
    }

    /**
     * 调用strtotime
     * @param mixed ...$args
     */
    public function callStrtotime(...$args)
    {
    }

    /**
     * 调用Time
     * @param mixed ...$args
     */
    public function callTime(...$args)
    {
    }

    /**
     * 清空所有已设置的mock
     */
    public function clearAll()
    {
        self::$callMap = [];
        self::$callMapBack = [];
    }

    /**
     * 清除某一项mock
     * @param $identify
     */
    public function clear($identify)
    {
        if (isset(self::$callMap[$identify])) {
            unset(self::$callMap[$identify]);
        }
        if (isset(self::$callMapBack[$identify])) {
            unset(self::$callMapBack[$identify]);
        }
    }

    /**
     * 暂停mock
     */
    public function pause()
    {
        self::$callMapBack = self::$callMap;
        self::$callMap = [];
    }

    /**
     * 暂停后重新开始
     */
    public function restart()
    {
        self::$callMap = self::$callMapBack;
    }
}
