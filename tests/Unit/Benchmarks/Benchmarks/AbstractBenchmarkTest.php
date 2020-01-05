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
use BenchmarkPHP\Exceptions\WrongArgumentException;
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
    public function testConstructorThrowsExceptionWhenWrongIterationNumber()
    {
        $options = [
            'iterations' => -1,
        ];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage('The number of iterations');

        new Integers($options);
    }

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

    public function testHasValidIterationsReturnsExpectedWhenValid()
    {
        $options = [
            'iterations' => 1,
        ];

        $this->assertTrue($this->runPrivateMethod($this->bench, 'hasValidIterations', [$options]));
    }

    public function testHasValidIterationsReturnsExpectedWhenInvalid()
    {
        $options = [
            'iterations' => null,
        ];

        $this->assertFalse($this->runPrivateMethod($this->bench, 'hasValidIterations', [$options]));
    }
}
