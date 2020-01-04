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

use BenchmarkPHP\Exceptions\BenchmarkRuntimeException;

trait HandlesFunctionsTrait
{
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
    protected function initFunctions(array $functions)
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
            $skipped = count(self::FUNCTIONS) - $executed;

            $summary = [
                'execute' => $this->generatePluralizedCount($executed),
                'skipped' => $this->generatePluralizedCount($skipped),
                'iterate' => $this->generatePluralizedCount($this->iterations, 'time'),
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

        if (!empty($diff = array_diff(self::FUNCTIONS, $this->functions))) {
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
}
