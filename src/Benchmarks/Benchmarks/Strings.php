<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks\Benchmarks;

use BenchmarkPHP\Exceptions\WrongArgumentException;

class Strings extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const FUNCTIONS = [
        'addslashes',
        'chunk_split',
        'count_chars',
        'crc32',
        'html_entity_decode',
        'htmlentities',
        'htmlspecialchars',
        'is_string',
        'ltrim',
        'md5',
        'metaphone',
        'rtrim',
        'sha1',
        'soundex',
        'str_shuffle',
        'str_split',
        'str_word_count',
        'strip_tags',
        'stripslashes',
        'strlen',
        'strrev',
        'strtolower',
        'strtoupper',
        'trim',
        'ucfirst',
        'ucwords',
        'BenchmarkPHP\Benchmarks\Benchmarks\Strings\castToBool',
        'BenchmarkPHP\Benchmarks\Benchmarks\Strings\castToInteger',
        'BenchmarkPHP\Benchmarks\Benchmarks\Strings\castToFloat',
        'BenchmarkPHP\Benchmarks\Benchmarks\Strings\castToArray',
        'BenchmarkPHP\Benchmarks\Benchmarks\Strings\castToObject',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * @param array $options
     * @throws WrongArgumentException
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
        $originalString = "benchmark\'s PHP";
        $reversedString = strrev($originalString);

        for ($i = 1; $i <= $this->iterations; $i++) {
            if (($i % 2) === 0) {
                $data[$i] = sprintf($originalString . ' %d times', $i);
            } else {
                $data[$i] = sprintf('%d times ' . $reversedString, $i);
            }
        }

        return $data;
    }
}

namespace BenchmarkPHP\Benchmarks\Benchmarks\Strings;

function castToBool($string)
{
    return (bool)$string;
}

function castToInteger($string)
{
    return (int)$string;
}

function castToFloat($string)
{
    return (float)$string;
}

function castToArray($string)
{
    return (array)$string;
}

function castToObject($string)
{
    return (object)$string;
}
