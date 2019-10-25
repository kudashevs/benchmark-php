<?php

namespace BenchmarkPHP\Benchmarks;

abstract class AbstractBenchmark
{
    /**
     * @var int
     */
    protected $iterations = 100000;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * Benchmarks contract.
     */
    abstract public function before();

    abstract public function handle();

    abstract public function after();

    /**
     * AbstractBenchmark constructor, better to use it.
     *
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws \LogicException
     * @return void
     */
    protected function init()
    {
        if ($this->iterations < 1) {
            throw new \LogicException('Number of iterations cannot be less than 1.');
        }
    }

    /**
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }
}
