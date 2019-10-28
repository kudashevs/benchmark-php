<?php

namespace BenchmarkPHP\Benchmarks;

class Strings extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const INIT_FUNCTIONS = [
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

        $this->functions = $this->initFunctions(self::INIT_FUNCTIONS);
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
