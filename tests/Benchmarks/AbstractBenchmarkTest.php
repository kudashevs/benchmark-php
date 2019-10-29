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
    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenOneResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedCount');

        $this->assertEquals('1 function', $method->invokeArgs($this->bench, [1]));
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenThreeResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedCount');

        $this->assertEquals('3 functions', $method->invokeArgs($this->bench, [3]));
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenFourResultAndTextWithExtraS()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedCount');

        $this->assertEquals('4 tests', $method->invokeArgs($this->bench, [4, 'tests']));
    }
}
