<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks\Benchmarks;

class Integers extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const FUNCTIONS = [
        'abs',
        'decbin',
        'dechex',
        'decoct',
        'is_int',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\inc',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\dec',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\addition',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\subtraction',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\multiplication',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\division',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\castToBool',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\castToFloat',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\castToString',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\castToArray',
        'BenchmarkPHP\Benchmarks\Benchmarks\Integers\castToObject',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * Create a new Integers instance.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->functions = $this->initFunctions(self::FUNCTIONS);
    }

    /**
     * @return array
     */
    protected function generateTestData()
    {
        $data = [];
        $evenAddition = 10;
        $oddAddition = 10;

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = $i + $evenAddition;
            } else {
                $data[$i] = $i - $oddAddition;
            }
        }

        return $data;
    }
}

namespace BenchmarkPHP\Benchmarks\Benchmarks\Integers;

function inc($num)
{
    return ++$num;
}

function dec($num)
{
    return --$num;
}

function addition($num)
{
    return $num + 42;
}

function subtraction($num)
{
    return $num - 42;
}

function multiplication($num)
{
    return $num * 3;
}

function division($num)
{
    return $num / 3;
}

function castToBool($num)
{
    return (bool)$num;
}

function castToFloat($num)
{
    return (float)$num;
}

function castToString($num)
{
    return (string)$num;
}

function castToArray($num)
{
    return (array)$num;
}

function castToObject($num)
{
    return (object)$num;
}
