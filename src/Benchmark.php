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
        'total_time' => 0,
        'started_at' => 'Not handled',
        'stopped_at' => 'Not handled',
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
        echo $this->reporter->showBlock($this->handleBenchmarks());
        echo $this->reporter->showSeparator();
        echo $this->reporter->showBlock($this->statistics);
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function initBenchmarks()
    {
        $names = ['math_integers']; // 'math_floats', 'strings', 'arrays'
        $benchmarks = [];

        foreach ($names as $name) {
            $class = '\\BenchmarkPHP\\Benchmarks\\' . $this->generateClassName($name);

            if (class_exists($class) && is_subclass_of($class, AbstractBenchmark::class)) {
                try {
                    $instance = new $class();
                } catch (\Exception $e) {
                    throw new \LogicException('Cannot instantiate benchmark ' . $class . ' with message ' . $e->getMessage() . '. Check Benchmarks folder.');
                }
                $benchmarks[$name] = $instance;
            }
        }

        if (empty($benchmarks)) { // should I throw?
            throw new \LogicException('Cannot find any benchmarks classes. Check Benchmarks folder.');
        }

        return $benchmarks;
    }

    /**
     * Handle benchmarks collection and collect results.
     *
     * @return array
     */
    protected function handleBenchmarks()
    {
        $this->beforeHandle(); // turn off cache, gc, etc.

        // @var AbstractBenchmark
        foreach ($this->benchmarks as $name => $benchmark) {
            $benchmark->before();

            $startTime = microtime(true);
            $benchmark->handle();
            $stopTime = microtime(true);

            $benchmark->after();

            $diffTime = $stopTime - $startTime;
            $this->statistics['total_time'] += $diffTime;

            echo $this->reporter->showBlock([$name => $diffTime]);
        }

        $this->afterHandle(); // clean, etc.

        return ['done' => $this->generateBenchmarkCount(count($this->benchmarks)) . ' test completed'];
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
    public function getStartedAt()
    {
        return ['Started at' => $this->statistics['started_at']];
    }

    /**
     * @return array
     */
    public function getStoppedAt()
    {
        return ['Stopped at' => $this->statistics['stopped_at']];
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
