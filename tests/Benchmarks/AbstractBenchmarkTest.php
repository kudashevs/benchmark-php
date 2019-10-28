<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use BenchmarkPHP\Benchmarks\AbstractBenchmark;
use BenchmarkPHP\Benchmarks\Integers;
use BenchmarkPHP\Tests\TestHelpersTrait;
use PHPUnit\Framework\TestCase;

class AbstractBenchmarkTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Integers $bench Must implement AbstractBenchmark */
    protected $bench;

    protected function setUp()
    {
        $this->bench = new Integers();

        if (!$this->bench instanceof AbstractBenchmark) {
            throw new \LogicException(get_class($this->bench) . ' doesn\'t extend AbstractBenchmark. Check setUp() method.');
        }
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
