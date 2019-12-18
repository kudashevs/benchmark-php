<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks;

class Arrays extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const FUNCTIONS = [
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
        'BenchmarkPHP\Benchmarks\Arrays\castToBool',
        'BenchmarkPHP\Benchmarks\Arrays\castToInteger',
        'BenchmarkPHP\Benchmarks\Arrays\castToFloat',
        'BenchmarkPHP\Benchmarks\Arrays\castToObject',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * Create a new Arrays instance.
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

namespace BenchmarkPHP\Benchmarks\Arrays;

function castToBool($array)
{
    return (bool)$array;
}

function castToInteger($array)
{
    return (int)$array;
}

function castToFloat($array)
{
    return (float)$array;
}

function castToObject($array)
{
    return (object)$array;
}
