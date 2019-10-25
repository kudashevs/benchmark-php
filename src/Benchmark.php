<?php

namespace BenchmarkPHP;

use BenchmarkPHP\Reporters\Reporter;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class Benchmark
{
    const VERSION = '1.0.0-beta';

    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    private $benchmarks = [];

    /**
     * @var Reporter
     */
    private $reporter;

    /**
     * @var array
     */
    private $statistics = [
        'started_at' => 'Not handled yet',
        'stopped_at' => 'Not handled yet',
        'completed' => 0,
        'skipped' => 0,
        'total_time' => 0,
    ];

    /**
     * Create a new Benchmark instance.
     *
     * @param Reporter $reporter
     * @return void
     */
    public function __construct(Reporter $reporter)
    {
        $this->reporter = $reporter;
        $this->benchmarks = $this->initBenchmarks();
    }

    /**
     * Aggregate and print data.
     *
     * @return void
     */
    public function run()
    {
        echo $this->reporter->showHeader($this->getBenchmarkVersion());
        echo $this->reporter->showBlock($this->getSystemInformation());
        echo $this->reporter->showSeparator();
        $this->handleBenchmarks();
        echo $this->reporter->showSeparator();
        echo $this->reporter->showBlock($this->getHandleStatistics());
        echo $this->reporter->showFooter($this->getStatisticsForHumans(['started_at', 'stopped_at', 'total_time']));
    }

    /**
     * @return array
     */
    protected function initBenchmarks()
    {
        $names = ['math_integers', 'math_floats', 'strings']; // 'arrays', 'objects'
        $benchmarks = [];

        foreach ($names as $name) {
            $class = '\\BenchmarkPHP\\Benchmarks\\' . $this->generateClassName($name);

            if (class_exists($class)) {
                try {
                    $instance = new $class();
                } catch (\Exception $e) {
                    $instance = 'failed';
                }
                $benchmarks[$name] = $instance;
            }
        }

        return $benchmarks;
    }

    /**
     * Handle benchmarks collection and collect results.
     *
     * @return void
     */
    protected function handleBenchmarks()
    {
        $this->beforeHandle(); // turn off cache, gc, etc.

        // @var AbstractBenchmark|string $benchmark
        foreach ($this->benchmarks as $name => $benchmark) {
            if (!is_object($benchmark) || !$benchmark instanceof AbstractBenchmark) {
                $this->benchmarkSkipped([$name => 'skipped']);

                continue;
            }

            $benchmark->before();

            $startTime = microtime(true);
            $benchmark->handle();
            $stopTime = microtime(true);

            $diffTime = $stopTime - $startTime;
            $this->statistics['total_time'] += $diffTime;

            $benchmark->after();

            $this->benchmarkCompleted([$name => $diffTime]);
        }

        $this->afterHandle(); // clean, etc.
    }

    /**
     * @return void
     */
    protected function beforeHandle()
    {
        $this->statistics['started_at'] = date(self::DATE_FORMAT);
    }

    /**
     * @return void
     */
    protected function afterHandle()
    {
        $this->statistics['stopped_at'] = date(self::DATE_FORMAT);
    }

    /**
     * Generate class name from a benchmark key.
     *
     * @param string $name
     * @return string
     */
    private function generateClassName($name)
    {
        $words = explode('_', $name);

        if (empty($words)) {
            return '';
        }

        $className = '';
        foreach ($words as $word) {
            $className .= ucfirst($word);
        }

        return $className;
    }

    /**
     * @param array $information
     * @return void
     */
    protected function benchmarkCompleted(array $information)
    {
        ++$this->statistics['completed'];

        echo $this->reporter->showBlock($information);
    }

    /**
     * @param array $information
     * @return void
     */
    protected function benchmarkSkipped(array $information)
    {
        ++$this->statistics['skipped'];

        echo $this->reporter->showBlock($information);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getStatistics(array $keys = [])
    {
        if (empty($keys)) {
            return $this->statistics;
        }

        return array_intersect_key($this->statistics, array_flip($keys));
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getStatisticsForHumans(array $keys = [])
    {
        $result = $this->getStatistics($keys);

        if (empty($result)) {
            return $result;
        }

        $updated = [];

        array_walk($result, function ($v, $k) use (&$updated) {
            $newKey = ucfirst(str_replace('_', ' ', $k));
            $updated[$newKey] = $v;
        });

        return $updated;
    }

    /**
     * @return array
     */
    public function getHandleStatistics()
    {
        list($completed, $skipped) = array_values($this->getStatistics(['completed', 'skipped']));

        return ($skipped > 0) ?
            [
                'done' => $this->generateBenchmarkCount($completed) . ' completed',
                'skip' => $this->generateBenchmarkCount($skipped) . ' skipped',
            ]
            :
            [
                'done' => $this->generateBenchmarkCount($completed) . ' completed',
            ];
    }

    /**
     * @param int $count
     * @return string
     */
    protected function generateBenchmarkCount($count)
    {
        return ($count > 1) ? $count . ' tests' : $count . ' test';
    }

    /**
     * @return array
     */
    protected function getBenchmarkVersion()
    {
        return ['Benchmark PHP ' . self::VERSION];
    }

    /**
     * @return array
     */
    public function getSystemInformation()
    {
        $result = [
            'Server' => $this->getHostInformation(),
            'PHP version' => phpversion(),
            'Zend version' => zend_version(),
            'Platform' => $this->getPlatformInformation(),
        ];

        return $result;
    }

    /**
     * @return string
     */
    protected function getHostInformation()
    {
        $hostName = (($host = gethostname()) !== false) ? $host : '?';
        $ipAddress = ($ip = gethostbyname($hostName)) ? $ip : '?';

        return $hostName . '@' . $ipAddress;
    }

    /**
     * @return string
     */
    protected function getPlatformInformation()
    {
        return PHP_OS . ' (' . php_uname('m') . ')';
    }
}
