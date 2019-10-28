<?php

namespace BenchmarkPHP\Benchmarks;

class Floats extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

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
     * Create a new Floats instance.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->functions = $this->initFunctions($this->functions);
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
