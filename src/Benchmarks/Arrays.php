<?php

namespace BenchmarkPHP\Benchmarks;

class Arrays extends AbstractBenchmark
{
    private $functions = [
        'array_change_key_case',
        'array_count_values',
        'array_filter',
        'array_flip',
        'array_keys',
        'array_multisort',
        'array_pop',
        'array_product',
        'array_rand',
        'array_reverse',
        'array_shift',
        'array_sum',
        'array_unique',
        'array_values',
        'arsort',
        'asort',
        'count',
        'is_array',
        'krsort',
        'ksort',
        'natsort',
        'rsort',
        'shuffle',
        'sort',
        'BenchmarkPHP\Benchmarks\convertToObject',
    ];

    /**
     * Create a new Arrays instance.
     *
     * @param array $options
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
            'exec_time' => $diffTime,
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
        $assocArray = [
            'Assembly' => 'low-level programming language',
            'SmallTalk' => 'high-level programming language',
            'LISP' => 'high-level programming language',
            'Cobol' => 'high-level programming language',
            'Pascal' => 'middle-level programming language',
            'C/C++' => 'middle-level programming language',
            'Java' => 'high-level programming language',
            'Rust' => 'high-level programming language',
            'C#' => 'high-level programming language',
            'Go' => 'high-level programming language',
            'JavaScript' => 'high-level programming language',
            'Python' => 'high-level programming language',
            'Ruby' => 'high-level programming language',
            'PHP' => 'high-level programming language',
            'Perl' => 'high-level programming language',
            'SQL' => 'high-level programming language',
        ];
        $indexArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 42, 42];

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = $assocArray;
            } else {
                $data[$i] = $indexArray;
            }
        }

        return $data;
    }
}

function convertToObject(array $arr)
{
    return (object)$arr;
}
