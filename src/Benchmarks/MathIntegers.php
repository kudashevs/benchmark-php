<?php

namespace BenchmarkPHP\Benchmarks;

class MathIntegers extends AbstractBenchmark
{
    private $functions = [
        'abs',
        'decbin',
        'dechex',
        'decoct',
        'is_int',
        'BenchmarkPHP\Benchmarks\inc',
        'BenchmarkPHP\Benchmarks\dec',
        'BenchmarkPHP\Benchmarks\addition42',
        'BenchmarkPHP\Benchmarks\subtraction42',
        'BenchmarkPHP\Benchmarks\multiplication3',
        'BenchmarkPHP\Benchmarks\division3',
    ];

    /**
     * Create a new MathIntegers instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->functions = $this->initFunctions($this->functions);
    }

    /**
     * @param array $functions
     * @throws \LogicException
     * @return array
     */
    protected function initFunctions(array $functions)
    {
        foreach ($functions as $key => $function) {
            if (!function_exists($function)) {
                unset($functions[$key]);
            }
        }

        if (empty($functions)) {
            throw new \LogicException('There is no functions to proceed.');
        }

        return $functions;
    }

    /**
     * @return void
     */
    public function before()
    {
        $this->data = $this->generateData();
    }

    /**
     * @return void
     */
    public function handle()
    {
        foreach ($this->functions as $function) {
            foreach ($this->data as $i) {
                $function($i);
            }
        }
    }

    /**
     * @return void
     */
    public function after()
    {
        $this->data = null;
    }

    /**
     * @return array
     */
    protected function generateData()
    {
        $data = [];
        $stepOdd = 10;
        $stepEven = 10;

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = $i + $stepEven;
            } else {
                $data[$i] = $i - $stepOdd;
            }
        }

        return $data;
    }
}

function inc($i)
{
    return ++$i;
}

function dec($i)
{
    return --$i;
}

function addition42($i)
{
    return $i + 42;
}

function subtraction42($i)
{
    return $i - 42;
}

function multiplication3($i)
{
    return $i * 3;
}

function division3($i)
{
    return $i / 3;
}
