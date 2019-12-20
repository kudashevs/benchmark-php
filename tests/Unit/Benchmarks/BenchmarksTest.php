<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Benchmarks;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class BenchmarksTest extends TestCase
{
    /** @var Benchmarks */
    private $benchmarks;

    protected function setUp()
    {
        $this->benchmarks = new Benchmarks();
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
    public function testGetInstantiatedReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->benchmarks->getInstantiated();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class));
        $this->assertCount($count, $benchmarks);
    }

    public function testGetBenchmarksReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->benchmarks->getBenchmarks();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }

    public function testListBenchmarksReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->benchmarks->listBenchmarks();

        $this->assertNotEmpty($benchmarks);
        $this->assertFalse(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }
}
