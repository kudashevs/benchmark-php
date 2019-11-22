<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Integers;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

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

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testConstructorUpdatesIterationsWhenIterationsAreInOptions()
    {
        $options = ['iterations' => 5];

        $bench = new Integers($options);

        $this->assertSame(5, $bench->getIterations());
    }

    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);

        $result = $this->runPrivateMethod($this->bench, 'isDebugMode');

        $this->assertTrue($result);
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isDebugMode');

        $this->assertFalse($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $result = $this->runPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertTrue($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertFalse($result);
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenOneResult()
    {
        $result = $this->runPrivateMethod($this->bench, 'generatePluralizedCount', [1]);

        $this->assertEquals('1 function', $result);
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenThreeResult()
    {
        $result = $this->runPrivateMethod($this->bench, 'generatePluralizedCount', [3]);

        $this->assertEquals('3 functions', $result);
    }

    public function testGeneratePluralizedCountReturnsExpectedWhenFourResultAndTextWithExtraS()
    {
        $result = $this->runPrivateMethod($this->bench, 'generatePluralizedCount', [4, 'tests']);

        $this->assertEquals('4 tests', $result);
    }
}
