<?php

namespace BenchmarkPHP\Benchmarks;

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
        'BenchmarkPHP\Benchmarks\Integers\inc',
        'BenchmarkPHP\Benchmarks\Integers\dec',
        'BenchmarkPHP\Benchmarks\Integers\addition',
        'BenchmarkPHP\Benchmarks\Integers\subtraction',
        'BenchmarkPHP\Benchmarks\Integers\multiplication',
        'BenchmarkPHP\Benchmarks\Integers\division',
        'BenchmarkPHP\Benchmarks\Integers\castToBool',
        'BenchmarkPHP\Benchmarks\Integers\castToFloat',
        'BenchmarkPHP\Benchmarks\Integers\castToString',
        'BenchmarkPHP\Benchmarks\Integers\castToArray',
        'BenchmarkPHP\Benchmarks\Integers\castToObject',
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

namespace BenchmarkPHP\Benchmarks\Integers;

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