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
        $this->data = $this->generateTestData();
    }

    /**
     * @return void
     */
    public function handle()
    {
        $startTime = microtime(true);

        foreach ($this->functions as $function) {
            foreach ($this->data as $i) {
                $function($i);
            }
        }

        $stopTime = microtime(true);
        $diffTime = $stopTime - $startTime;

        $this->statistics = [
            'start_time' => $startTime,
            'stop_time' => $stopTime,
            'diff_time' => $diffTime,
        ];
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
    public function result()
    {
        return $this->statistics;
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
