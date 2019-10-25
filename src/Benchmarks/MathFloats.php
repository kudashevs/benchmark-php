<?php

namespace BenchmarkPHP\Benchmarks;

class MathFloats extends AbstractBenchmark
{
    private $functions = [
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
    ];

    /**
     * Create a new MathFloats instance.
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
        $this->data = range(1.0, (float)$this->iterations);
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
        $stepOdd = 0.33333333333;
        $stepEven = 0.42;
        $data = [];

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = $i + $stepEven;
            } else {
                $data[$i] = $i + $stepOdd;
            }
        }

        return $data;
    }
}