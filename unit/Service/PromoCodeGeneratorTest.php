<?php

namespace Tests\Unit\Service;

class PromoCodeGeneratorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $str;

    /**
     * @var int
     */
    protected $sizeString;

    /**
     * @var mixed
     */
    protected $resolveInstance;

    /**
     * setUp метод
     */
    protected function setUp()
    {
        $this->resolveInstance = resolve('App\Contracts\PromoCodeGenerator');
        $this->sizeString = 32;
        $this->str = 'aaaff1112323';
    }

    public function testInstance()
    {
        $this->assertInstanceOf('App\Service\PromoCodeGenerator', $this->resolveInstance);
    }

    public function testRunMethod()
    {
        $this->assertTrue(method_exists($this->resolveInstance, 'run'));
    }

    public function testOneParameter()
    {
        $code = $this->resolveInstance->run($this->sizeString);
        $this->assertTrue(is_string($code));
        $this->assertEquals(strlen($code), $this->sizeString);
    }

    public function testTwoParameters()
    {
        $code = $this->resolveInstance->run($this->sizeString, $this->str);
        $this->assertTrue(is_string($code));
        $this->assertEquals(strlen($code), $this->sizeString);
    }
}