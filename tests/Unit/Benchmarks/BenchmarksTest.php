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
    public function testGetInstancesReturnsExpectedWhenEmptyOptions()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->getInstances();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class));
        $this->assertCount($count, $benchmarks);
    }

    public function testGetInstancesPassesOptionsToBenchmarkInstance()
    {
        $options = ['verbose' => 'updated'];

        $benchmarks = $this->repository->getInstances($options);

        $instance = current($benchmarks);
        $this->assertInstanceOf(AbstractBenchmark::class, $instance);
        $this->assertEquals($options, $instance->getOptions());
    }

    public function testGetInstancesReturnsExpectedWhenBenchmarksIncludeNotExist()
    {
        $options['benchmarks'] = ['integers' => 0, 'floats' => 1, 'not_exist' => 2];

        $benchmarks = $this->repository->getInstances($options);

        $this->assertNotEmpty($benchmarks);
        $this->assertCount(2, $benchmarks);
        $this->assertFalse(array_key_exists('not_exist', $benchmarks));
    }

    public function testGetReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->get();

        $this->assertNotEmpty($benchmarks);
        $this->assertTrue(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }

    public function testGetNamesReturnsExpected()
    {
        $count = count(Benchmarks::BENCHMARKS);

        $benchmarks = $this->repository->getNames();

        $this->assertNotEmpty($benchmarks);
        $this->assertFalse(is_a(current($benchmarks), AbstractBenchmark::class, true));
        $this->assertCount($count, $benchmarks);
    }
}
