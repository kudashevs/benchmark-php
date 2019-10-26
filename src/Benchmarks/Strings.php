<?php

namespace BenchmarkPHP\Benchmarks;

class Strings extends AbstractBenchmark
{
    private $functions = [
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
     * Create a new Strings instance.
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
        $originalString = "benchmark\'s PHP";
        $reversedString = strrev($originalString);
        $data = [];

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
