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
    private $repository;

    protected function setUp()
    {
        $this->repository = new Benchmarks();
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
    public function testGetInstantiatedReturnsExpectedWhenEmptyOptions()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->getInstantiated();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class));
        $this->assertCount($count, $benchmarks);
    }

    public function testGetInstatiatedReturnsExpectedWhenBenchmarksIncludeNotExist()
    {
        $options['benchmarks'] = ['integers' => 0, 'floats' => 1, 'not_exist' => 2];

        $benchmarks = $this->repository->getInstantiated($options);

        $this->assertNotEmpty($benchmarks);
        $this->assertCount(2, $benchmarks);
        $this->assertFalse(array_key_exists('not_exist', $benchmarks));
    }

    public function testGetBenchmarksReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->getBenchmarks();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }

    public function testGetBenchmarksNamesReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->getBenchmarksNames();

        $this->assertNotEmpty($benchmarks);
        $this->assertFalse(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }
}
