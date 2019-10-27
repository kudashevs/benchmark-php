<?php

namespace BenchmarkPHP;

use BenchmarkPHP\Reporters\Reporter;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class Benchmark
{
    const VERSION = '1.0.0-beta';

    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Reporter
     */
    private $reporter;

    /**
     * @var array
     */
    private $options = [
        'verbose' => false,
        'debug' => false,
    ];

    /**
     * @var array
     */
    private $benchmarks = [];

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
     */
    public function __construct(Reporter $reporter)
    {
        $this->reporter = $reporter;
        $this->options = $this->initOptions($_SERVER['argv']);
        $this->benchmarks = $this->initBenchmarks();
    }

    /**
     * Aggregate and print data.
     *
     * @return void
     */
    public function run()
    {
        $this->reporter->showHeader($this->getBenchmarkFullName());
        $this->reporter->showBlock($this->getSystemInformation());
        $this->reporter->showSeparator();
        $this->handleBenchmarks();
        $this->reporter->showSeparator();
        $this->reporter->showBlock($this->getHandleStatistics());
        $this->reporter->showFooter($this->getStatisticsForHumans(['started_at', 'stopped_at', 'total_time']));
    }

    /**
     * @param array $arguments
     * @return array
     */
    protected function initOptions(array $arguments)
    {
        array_shift($arguments);

        if (empty($arguments)) {
            return $this->options;
        }

        $options = [];

        foreach ($arguments as $argument) {
            switch ($argument) {
                case '--version':
                    $this->reporter->showBlock($this->getBenchmarkFullName());
                    $this->terminateWithCode();

                    break;

                case '--verbose':
                    $options['verbose'] = true;

                    break;

                case '--debug':
                    $options['debug'] = true;

                    break;

                default:
                    $this->reporter->showBlock($this->getBenchmarkFullName() . PHP_EOL);
                    $this->terminateWithMessage('Unknown option ' . $argument . PHP_EOL);

                    break;
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function initBenchmarks()
    {
        $names = ['math_integers', 'math_floats', 'strings', 'arrays', 'objects']; // 'filesystem', 'network'
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
                $this->benchmarkSkipped($name);

                continue;
            }

            $benchmark->before();

            $benchmark->handle();

            $benchmark->after();

            $this->benchmarkCompleted($name, $benchmark->result());
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
     * @param string $name
     * @return void
     */
    protected function benchmarkSkipped($name)
    {
        ++$this->statistics['skipped'];

        $message = [
            $name => 'skipped',
        ];

        $this->reporter->showBlock($message);
    }

    /**
     * @param string $name
     * @param array $information
     * @return void
     */
    protected function benchmarkCompleted($name, array $information = [])
    {
        ++$this->statistics['completed'];

        if ($this->hasCorrectExecutionTime($information)) {
            $this->statistics['total_time'] += $information['exec_time'];
            $executionTime = $information['exec_time'];
        } else {
            $executionTime = 'time is malformed';
        }

        $message = [
            $name => $executionTime,
        ];

        $this->reporter->showBlock($message);
    }

    /**
     * @param array $information
     * @return bool
     */
    protected function hasCorrectExecutionTime(array $information)
    {
        return isset($information['exec_time']) && is_numeric($information['exec_time']);
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

        $resultPreservedKeysOrder = [];

        foreach (array_flip($keys) as $k => $v) {
            if (array_key_exists($k, $this->statistics)) {
                $resultPreservedKeysOrder[$k] = $this->statistics[$k];
            }
        }

        return $resultPreservedKeysOrder;
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

        foreach ($result as $k => $v) {
            $newKey = ucfirst(str_replace('_', ' ', $k));
            $updated[$newKey] = $v;
        }

        return $updated;
    }

    /**
     * @return array
     */
    public function getHandleStatistics()
    {
        list($completed, $skipped) = array_values($this->getStatistics(['completed', 'skipped']));

        return ($skipped > 0)
            ? [
                'done' => $this->generateBenchmarkCount($completed) . ' completed',
                'skip' => $this->generateBenchmarkCount($skipped) . ' skipped',
            ]
            : [
                'done' => $this->generateBenchmarkCount($completed) . ' completed',
            ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getBenchmarkFullName()
    {
        return 'Benchmark PHP ' . $this->getBenchmarkVersion();
    }

    /**
     * @return string
     */
    protected function getBenchmarkVersion()
    {
        return self::VERSION;
    }

    /**
     * @param int $count
     * @return string
     */
    protected function generateBenchmarkCount($count)
    {
        return ($count > 1) ? $count . ' benchmarks' : $count . ' benchmark';
    }

    /**
     * @param int $code
     * @return void
     */
    protected function terminateWithCode($code = 0)
    {
        exit($code);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function terminateWithMessage($message = '')
    {
        exit($message);
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
