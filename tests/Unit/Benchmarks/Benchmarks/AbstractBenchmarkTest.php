<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Benchmarks\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\Benchmarks\Integers;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class AbstractBenchmarkTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Integers $bench Class must extend AbstractBenchmark */
    protected $bench;

    protected function setUp()
    {
        $this->bench = new Integers();
    }

    /**
     * Mandatory test.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(AbstractBenchmark::class, $this->bench);
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
