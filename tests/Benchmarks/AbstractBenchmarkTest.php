<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class AbstractBenchmarkTest extends TestCase
{
    use TestHelpersTrait;

    /** @var AbstractBenchmark */
    protected $bench;

    protected function setUp()
    {
        $this->bench = $this->getMockForAbstractClass(AbstractBenchmark::class);
    }

    // Exceptions.

    // Corner cases.

    // Functionality.
    public function testIsDebugModeReturnExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsDebugModeReturnExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertFalse($method->invoke($this->bench));
    }
}
