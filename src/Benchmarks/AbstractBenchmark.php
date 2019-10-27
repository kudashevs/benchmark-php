<?php

namespace BenchmarkPHP\Benchmarks;

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
        'start_time' => 'Not handled yet',
        'stop_time' => 'Not handled yet',
        'diff_time' => 'Not handled yet',
    ];

    /**
     * AbstractBenchmark constructor, better to use it.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->initBenchmark($options);
    }

    /**
     * @param array $options
     * @throws \LogicException
     * @return void
     */
    protected function initBenchmark(array $options)
    {
        if ($this->iterations < 1) {
            throw new \LogicException('Number of iterations cannot be less than 1.');
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
