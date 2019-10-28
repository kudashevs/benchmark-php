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
        'debug' => false,
        'verbose' => false,
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
        $this->reporter->showHeader($this->getFullTitle());

        $this->reporter->showBlock($this->getSystemInformation());
        $this->reporter->showSeparator();

        $this->handleBenchmarks();

        if ($this->isVerboseMode() || $this->isDebugMode() || $this->hasSkippedBenchmarks()) {
            $this->reporter->showSeparator();
            $this->reporter->showBlock($this->getBenchmarksSummary());
        }

        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $this->reporter->showFooter($this->getStatisticsForHumans(['started_at', 'stopped_at', 'total_time']));
        } else {
            $this->reporter->showFooter($this->getStatisticsForHumans(['total_time']));
        }
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
                case '--debug':
                    $options['debug'] = true;

                    break;

                case '--help':
                    $this->reporter->showBlock($this->getHelp());
                    $this->terminateWithCode(0);

                    break;

                case '--verbose':
                    $options['verbose'] = true;

                    break;

                case '--version':
                    $this->reporter->showBlock($this->getFullTitle());
                    $this->terminateWithCode(0);

                    break;

                default:
                    $this->reporter->showBlock($this->getFullTitle() . PHP_EOL);
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
        $names = ['integers', 'floats', 'strings', 'arrays', 'objects']; // 'filesystem', 'db', 'network'
        $benchmarks = [];

        foreach ($names as $name) {
            $class = '\\BenchmarkPHP\\Benchmarks\\' . $this->generateClassName($name);

            if (class_exists($class)) {
                try {
                    $instance = new $class($this->options);
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
                $information = [
                    'type' => gettype($benchmark),
                    'class' => is_object($benchmark) ? get_class($benchmark) : 'not an object',
                ];
                $this->benchmarkSkipped((string)$name, $information);

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
     * @param array $information
     * @return void
     */
    protected function benchmarkSkipped($name, array $information = [])
    {
        ++$this->statistics['skipped'];

        $message = [
            $name => 'skipped',
        ];

        if ($this->isDebugMode()) {
            $message = array_merge($message, $information);
        }

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

        if ($this->isDebugMode()) {
            $message = array_merge([$name => 'completed'], $information);
        }

        $this->reporter->showBlock($message);
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
     * @return bool
     */
    protected function hasSkippedBenchmarks()
    {
        return isset($this->statistics['skipped']) && ($this->statistics['skipped'] > 0);
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
    public function getBenchmarksSummary()
    {
        list($completed, $skipped) = array_values($this->getStatistics(['completed', 'skipped']));

        $summary = ['done' => $this->generatePluralizedCount($completed) . ' completed'];

        if ($this->hasSkippedBenchmarks()) {
            $summary = array_merge($summary, ['skip' => $this->generatePluralizedCount($skipped) . ' skipped']);
        }

        return $summary;
    }

    /**
     * @param int $count
     * @return string
     */
    protected function generatePluralizedCount($count)
    {
        return ($count > 1) ? $count . ' benchmarks' : $count . ' benchmark';
    }

    /**
     * @return string
     */
    public function getFullTitle()
    {
        return 'Benchmark PHP ' . $this->getVersion();
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $message = '';
        $message .= $this->getFullTitle() . str_repeat(PHP_EOL, 2);
        $message .= <<<EOT
Available Options:

  --debug           Prints miscellaneous information during execution.
  --help            Prints usage information and exits.
  --version         Prints the version and exits.
EOT;

        return $message;
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
