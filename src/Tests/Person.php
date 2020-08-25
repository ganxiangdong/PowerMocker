<?php
namespace PowerMocker\Tests;

/**
 * 被测试的对象
 */
class Person
{
    /**
     * 人名
     * @var string
     */
    private $name = 'Tom';

    /**
     * 获取人名
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
