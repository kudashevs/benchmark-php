<?php

namespace BenchmarkPHP\Benchmarks;

trait HandlesFunctionsTrait
{
    /**
     * @return void
     */
    public function handle()
    {
        $initTime = microtime(true);
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
            'start_time' => $initTime,
            'stop_time' => $initTime - $diffTime,
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
        $initKeys = ['exec_time'];
        $result = array_intersect_key($this->statistics, array_flip($initKeys));

        $result = array_merge($this->getFunctionsSummary(), $result);

        return $result;
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
            throw new \LogicException('There are no functions to proceed.');
        }

        return $functions;
    }

    /**
     * @return array
     */
    protected function getFunctionsSummary()
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
    protected function getFunctionsList()
    {
        $list = [];

        if (!empty($this->functions)) {
            $list['executed functions'] = PHP_EOL . implode(PHP_EOL, $this->cleanFunctionsNames($this->functions));
        }

        if (!empty($diff = array_diff(self::FUNCTIONS, $this->functions))) {
            $list['skipped functions'] = PHP_EOL . implode(PHP_EOL, $this->cleanFunctionsNames($diff));
        }

        return $list;
    }

    /**
     * @param array $functions
     * @return array
     */
    protected function cleanFunctionsNames(array $functions)
    {
        return array_map(function ($v) {
            return str_replace('BenchmarkPHP\\Benchmarks\\', '', $v);
        }, $functions);
    }
}
