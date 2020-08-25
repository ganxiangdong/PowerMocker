<?php
namespace PowerMocker\Tests;

include __DIR__.'/../../vendor/autoload.php';
\PowerMocker\AutoloadManager::instance()->includeComposerAutoload(__DIR__.'/../../vendor/autoload.php');

/**
 * Class TestMethod
 */
class TestMethod extends \PHPUnit\Framework\TestCase
{
    public function testOne()
    {
        new \PowerMocker\Transform();

        //设置要mock的方法
        \PowerMocker\Mock::instance()->method(\PowerMocker\Tests\Person::class, 'getName', 'PowerMocker');
        //调用
        $this->assertEquals('PowerMocker', (new \PowerMocker\Tests\Person())->getName());

        //暂停
        \PowerMocker\Mock::instance()->pause();
        //调用
        $this->assertEquals('Tom', (new \PowerMocker\Tests\Person())->getName());

        //重启
        \PowerMocker\Mock::instance()->restart();
        //调用
        $this->assertEquals('PowerMocker', (new \PowerMocker\Tests\Person())->getName());

        //清除
        \PowerMocker\Mock::instance()->clearAll();
        //调用
        $this->assertEquals('Tom', (new \PowerMocker\Tests\Person())->getName());
    }
}
