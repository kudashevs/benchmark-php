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
use BenchmarkPHP\Exceptions\BenchmarkRuntimeException;

abstract class AbstractFunctionsBasedBenchmark extends AbstractBenchmark
{
    /**
     * Default empty constant prevents Fatal error to be thrown
     * if there is no constant named FUNCTIONS in child class.
     * So we can catch a certain exception and handle it.
     *
     * @var array
     */
    const FUNCTIONS = [];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * @param array $options
     * @throws WrongArgumentException|BenchmarkRuntimeException
     */
    final public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->functions = $this->initFunctions(static::FUNCTIONS);
    }

    /**
     * @return void
     */
    public function handle()
    {
        $diffTime = 0;

        foreach ($this->functions as $function) {
            $startTime = microtime(true);

            foreach ($this->data as $value) {
                $function($value);
            }

            $stopTime = microtime(true);
            $diffTime += $stopTime - $startTime;
        }

        $this->statistics = [
            'exec_time' => $diffTime,
        ];
    }

    /**
     * @return void
     */
    public function before()
    {
        $this->data = $this->generateTestData();
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
    public function result()
    {
        $allowedKeys = ['exec_time'];
        $result = array_intersect_key($this->statistics, array_flip($allowedKeys));
        $result = array_merge($this->getFunctionsSummary(), $result);

        return $result;
    }

    /**
     * @param array $functions
     * @throws BenchmarkRuntimeException
     * @return array
     */
    private function initFunctions(array $functions)
    {
        foreach ($functions as $key => $function) {
            if (!function_exists($function)) {
                unset($functions[$key]);
            }
        }

        if (empty($functions)) {
            throw new BenchmarkRuntimeException('There are no functions to proceed.');
        }

        return $functions;
    }

    /**
     * @return array
     */
    private function getFunctionsSummary()
    {
        $summary = [];

        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $executed = count($this->functions);
            $skipped = count(static::FUNCTIONS) - $executed;

            $summary = [
                'execute' => $this->generatePluralized($executed, 'function'),
                'skipped' => $this->generatePluralized($skipped, 'function'),
                'iterate' => $this->generatePluralized($this->iterations, 'time'),
            ];
        }

        if ($this->isDebugMode()) {
            $summary = array_merge($summary, $this->getFunctionsList());
            $summary = array_merge($summary, $this->statistics);
        }

        return $summary;
    }

    /**
     * @return array
     */
    private function getFunctionsList()
    {
        $list = [];

        if (!empty($this->functions)) {
            $list['executed functions'] = PHP_EOL . implode(PHP_EOL, $this->getShortFunctionsNames($this->functions));
        }

        if (!empty($diff = array_diff(static::FUNCTIONS, $this->functions))) {
            $list['skipped functions'] = PHP_EOL . implode(PHP_EOL, $this->getShortFunctionsNames($diff));
        }

        return $list;
    }

    /**
     * @param array $functions
     * @return array
     */
    private function getShortFunctionsNames(array $functions)
    {
        return array_map(function ($v) {
            return str_replace('BenchmarkPHP\\Benchmarks\\', '', $v);
        }, $functions);
    }

    /**
     * @return array
     */
    abstract protected function generateTestData();
}
