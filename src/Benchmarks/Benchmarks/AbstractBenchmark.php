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

abstract class AbstractBenchmark
{
    /**
     * @var int
     */
    protected $iterations = 100000;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $statistics = [
        'exec_time' => 'Not handled yet',
    ];

    /**
     * AbstractBenchmark constructor, better to use it.
     *
     * @param array $options
     * @throws WrongArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initBenchmark($options);
    }

    /**
     * @param array $options
     * @throws WrongArgumentException
     * @return void
     */
    protected function initBenchmark(array $options)
    {
        $this->iterations = !empty($options['iterations']) ? $options['iterations'] : $this->iterations;

        if ($this->iterations < 1) {
            throw new WrongArgumentException('The number of iterations cannot be less than 1.');
        }

        if (isset($options['testing']) && $options['testing'] === true) {
            $this->iterations = 1;
        }

        $this->options = $options;
    }

    /**
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    protected function isDebugMode()
    {
        return isset($this->options['debug']) && $this->options['debug'] === true;
    }

    /**
     * @return bool
     */
    protected function isVerboseMode()
    {
        return isset($this->options['verbose']) && $this->options['verbose'] === true;
    }

    /**
     * @param int $count
     * @param string $text
     * @return string
     */
    protected function generatePluralizedCount($count, $text = 'function')
    {
        $text = trim($text);

        return ($count > 1) ? $count . ' ' . rtrim($text, 's') . 's' : $count . ' ' . $text;
    }

    /**
     * Benchmarks contract.
     */

    /**
     * Method is executed before benchmark handle method.
     *
     * @return void
     */
    abstract public function before();

    /**
     * Method does the main benchmarking work.
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Method is executed after benchmark handle method.
     *
     * @return void
     */
    abstract public function after();

    /**
     * Method returns benchmark statistics.
     *
     * @return array
     */
    abstract public function result();
}
