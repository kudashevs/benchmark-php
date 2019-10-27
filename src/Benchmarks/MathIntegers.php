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
        'BenchmarkPHP\Benchmarks\addition',
        'BenchmarkPHP\Benchmarks\subtraction',
        'BenchmarkPHP\Benchmarks\multiplication',
        'BenchmarkPHP\Benchmarks\division',
    ];

    /**
     * Create a new MathIntegers instance.
     *
     * @return void
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

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

    protected function handleOptions()
    {
        if (empty($this->options)) {
            return;
        }
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
