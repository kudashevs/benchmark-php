<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\MathIntegers;

class MathIntegerTest extends TestCase
{
    /** @var MathIntegers */
    private $bench;

    protected function setUp()
    {
        $this->bench = new MathIntegers();
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

    /**
     * Helpers.
     */
    public function getPrivateMethod($obj, $methodName)
    {
        $reflection = new \ReflectionClass($obj);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    public function setPrivateVariableValue($obj, $valueName, $newValue)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);
        $property->setValue($obj, $newValue);
    }

    public function getPrivateVariableValue($obj, $valueName)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
