<?php

namespace BenchmarkPHP\Benchmarks;

class Floats extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const FUNCTIONS = [
        'abs',
        'acos',
        'asin',
        'atan',
        'ceil',
        'cos',
        'exp',
        'floor',
        'is_float',
        'is_finite',
        'is_infinite',
        'log',
        'sin',
        'sqrt',
        'tan',
        'BenchmarkPHP\Benchmarks\Floats\inc',
        'BenchmarkPHP\Benchmarks\Floats\dec',
        'BenchmarkPHP\Benchmarks\Floats\addition',
        'BenchmarkPHP\Benchmarks\Floats\subtraction',
        'BenchmarkPHP\Benchmarks\Floats\multiplication',
        'BenchmarkPHP\Benchmarks\Floats\division',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * Create a new Floats instance.
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
        $evenAddition = 0.42;
        $oddAddition = 0.33333333333;

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = $i + $evenAddition;
            } else {
                $data[$i] = $i + $oddAddition;
            }
        }

        return $data;
    }
}

namespace BenchmarkPHP\Benchmarks\Floats;

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
    return $num + 42.24;
}

function subtraction($num)
{
    return $num - 42.24;
}

function multiplication($num)
{
    return $num * M_PI;
}

function division($num)
{
    return $num / M_PI;
}