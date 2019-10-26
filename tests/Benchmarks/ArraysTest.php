<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use BenchmarkPHP\Tests\TestHelpers;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Arrays;

class ArraysTest extends TestCase
{
    use TestHelpers;

    /** @var Arrays */
    private $bench;

    protected function setUp()
    {
        $this->bench = new Arrays();
    }

    /**
     * Exceptions.
     */
    public function testConstructorThrowExceptionWhenEmptyFunctions()
    {
        $this->expectException(\LogicException::class);

        $method = $this->getPrivateMethod($this->bench, 'initFunctions');

        $method->invokeArgs($this->bench, [[]]);
    }

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testBeforeCreatesData()
    {
        $this->bench->before();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertCount($this->bench->getIterations(), $data);
    }

    public function testAfterRemovesData()
    {
        $this->bench->before();
        $this->bench->after();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertEmpty($data);
    }

    public function testGenerateDataReturnsExpected()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateData');
        $data = $method->invoke($this->bench);

        $this->assertCount($this->bench->getIterations(), $data);
        $this->assertInternalType('array', $data[mt_rand(1, $this->bench->getIterations())]);
    }

}