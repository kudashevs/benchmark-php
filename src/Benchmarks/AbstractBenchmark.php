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

    /**
     * Benchmarks contract.
     */

    /**
     * Method will be executed before benchmark handle method.
     *
     * @return void
     */
    abstract public function before();

    /**
     * @return void
     */
    abstract public function handle();

    /**
     * Method will be executed after benchmark handle method.
     *
     * @return void
     */
    abstract public function after();
}
