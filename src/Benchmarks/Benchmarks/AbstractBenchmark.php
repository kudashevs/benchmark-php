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

use BenchmarkPHP\Traits\PluralizeTrait;
use BenchmarkPHP\Exceptions\WrongArgumentException;

abstract class AbstractBenchmark
{
    use PluralizeTrait {
        generatePluralized as protected;
    }

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
        $this->iterations = $this->hasValidIterations($options) ? $options['iterations'] : $this->iterations;

        /**
         * This additional check allows us to prevent wrong value assignment in passed options and in child classes.
         */
        if ($this->iterations < 1) {
            throw new WrongArgumentException('The number of iterations cannot be less than 1.');
        }

        if (isset($options['testing']) && $options['testing'] === true) {
            $this->iterations = 1;
        }

        $this->options = $options;
    }

    /**
     * @param mixed $options
     * @return bool
     */
    private function hasValidIterations(array $options)
    {
        return array_key_exists('iterations', $options) && !empty($options['iterations']) && is_int($options['iterations']);
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
