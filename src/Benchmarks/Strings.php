<?php

namespace BenchmarkPHP\Benchmarks;

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
        'BenchmarkPHP\Benchmarks\Strings\castToBool',
        'BenchmarkPHP\Benchmarks\Strings\castToInteger',
        'BenchmarkPHP\Benchmarks\Strings\castToFloat',
        'BenchmarkPHP\Benchmarks\Strings\castToArray',
        'BenchmarkPHP\Benchmarks\Strings\castToObject',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * Create a new Strings instance.
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

namespace BenchmarkPHP\Benchmarks\Strings;

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
