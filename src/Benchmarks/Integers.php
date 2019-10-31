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
        'BenchmarkPHP\Benchmarks\inc',
        'BenchmarkPHP\Benchmarks\dec',
        'BenchmarkPHP\Benchmarks\addition',
        'BenchmarkPHP\Benchmarks\subtraction',
        'BenchmarkPHP\Benchmarks\multiplication',
        'BenchmarkPHP\Benchmarks\division',
        'BenchmarkPHP\Benchmarks\castToBool',
        'BenchmarkPHP\Benchmarks\castToFloat',
        'BenchmarkPHP\Benchmarks\castToString',
        'BenchmarkPHP\Benchmarks\castToArray',
        'BenchmarkPHP\Benchmarks\castToObject',
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