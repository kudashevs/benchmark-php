<?php

namespace BenchmarkPHP;

use BenchmarkPHP\Reporters\Reporter;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class Benchmark
{
    /**
     * @var string
     */
    const VERSION = '1.0.0-beta';

    /**
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    const BENCHMARKS = [ // 'files', 'filesystem', 'database', 'network'
        'integers',
        'floats',
        'strings',
        'arrays',
        'objects',
    ];

    /**
     * @var array
     */
    const REQUIRES_VALUE = [
        '-b',
        '--benchmarks',
        '-i',
        '--iterations',
    ];

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
        $arguments = $this->initArguments($_SERVER['argv']);
        $this->options = $this->parseArguments($arguments);
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

        $this->reporter->showBlock($this->getBenchmarksSummary());
        $this->reporter->showFooter($this->getSummary(['total_time']));
    }

    /**
     * @param array $arguments
     * @return array
     */
    protected function initArguments(array $arguments)
    {
        array_shift($arguments);

        $result = [];

        if (empty($arguments)) {
            return $result;
        }

        while ($argument = current($arguments)) {
            $next = next($arguments);

            if (in_array($argument, self::REQUIRES_VALUE, true)) {
                $this->checkRequiredArgumentHasValue($argument, $next);
                $this->checkRequiredArgumentNotAnOption($argument, $next);
                $result[$argument] = $next;
                next($arguments);

                continue;
            }

            $result[$argument] = false;
        }

        return $result;
    }

    /**
     * @param string $argument
     * @param mixed $value
     * @return void
     */
    protected function checkRequiredArgumentHasValue($argument, $value)
    {
        if ($value === false) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a value. Empty value is passed.' . PHP_EOL);
        }
    }

    /**
     * @param array $arguments
     * @return array Return array of options
     */
    protected function parseArguments(array $arguments)
    {
        if (empty($arguments)) {
            return $this->options;
        }

        $options = [];

        foreach ($arguments as $argument => $value) {
            switch ($argument) {
                case '--debug':
                    $options['debug'] = true;

                    break;

                case '-v':
                case '--verbose':
                    $options['verbose'] = true;

                    break;

                case '--version':
                    $this->reporter->showBlock($this->getVersionString());
                    $this->terminateWithCode(0);

                    break;

                case '-l':
                case '--list':
                    $this->reporter->showBlock($this->getVersionString());
                    $this->reporter->showBlock($this->listBenchmarks('header'), 'list');
                    $this->terminateWithCode(0);

                    break;

                case '-h':
                case '--help':
                    $this->reporter->showBlock($this->getHelp());
                    $this->terminateWithCode(0);

                    break;

                case '-b':
                case '--benchmarks':
                    $options['benchmarks'] = $this->parseRequiredValueForBenchmarks($argument, $value);

                    break;

                case '-i':
                case '--iterations':
                    $options['iterations'] = $this->parseRequiredArgumentForIterations($argument, $value);

                    break;

                default:
                    $this->reporter->showBlock($this->getVersionString());
                    $this->terminateWithMessage('Unknown option ' . $argument . PHP_EOL);

                    break;
            }
        }

        return $options;
    }

    /**
     * @param string $argument
     * @param string $value
     * @return array
     */
    protected function parseRequiredValueForBenchmarks($argument, $value)
    {
        if (empty($value) || !is_string($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a value. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $this->checkRequiredArgumentNotAnOption($argument, $value);

        $benchmarks = explode(',', $value);

        if (!empty($undefined = array_diff($benchmarks, self::BENCHMARKS))) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires valid benchmarks names. Check ' . $this->generatePrintableWithSpace(implode(
                ',',
                $undefined
            )) . 'or use -l for more information.' . PHP_EOL);
        }

        return $benchmarks;
    }

    /**
     * @param string $argument
     * @param string $value
     * @return int
     */
    protected function parseRequiredArgumentForIterations($argument, $value)
    {
        $minIterations = 1;
        $maxIterations = 100000000;

        if (empty($value) || !is_numeric($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a value. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $iterations = (int)$value;

        if ($iterations < $minIterations || $iterations > $maxIterations) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires the value between ' . $minIterations . ' and ' . $maxIterations . '. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        return $iterations;
    }

    /**
     * @param string $argument
     * @param mixed $value
     */
    protected function checkRequiredArgumentNotAnOption($argument, $value)
    {
        if (strpos($value, '-') === 0) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a value. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function generatePrintableWithSpace($value)
    {
        return $this->generatePrintable($value) . ' ';
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function generatePrintable($value)
    {
        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @return array
     */
    protected function initBenchmarks()
    {
        $initialized = !empty($this->options['benchmarks']) ? $this->options['benchmarks'] : self::BENCHMARKS;
        $benchmarks = [];

        foreach ($initialized as $name) {
            $class = '\\BenchmarkPHP\\Benchmarks\\' . ucfirst($name);

            if (class_exists($class)) {
                try {
                    $instance = new $class($this->options);
                } catch (\Exception $e) {
                    $instance = [
                        'fail' => $e->getMessage(),
                    ];
                }
                $benchmarks[$name] = $instance;
            }
        }

        return $benchmarks;
    }

    /**
     * @param string $style
     * @return array
     */
    protected function listBenchmarks($style = '')
    {
        $benchmarks = [];

        foreach (self::BENCHMARKS as $name) {
            $class = '\\BenchmarkPHP\\Benchmarks\\' . ucfirst($name);

            if (class_exists($class)) {
                $benchmarks[] = $name;
            }
        }

        if ($style === 'header') {
            $benchmarks = array_merge(['exclude:Available ' . $this->generatePluralizedBenchmarkCount(count($benchmarks))], $benchmarks);
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
                $this->benchmarkSkipped($name, $benchmark);

                continue;
            }

            $benchmark->before();

            $benchmark->handle();

            $benchmark->after();

            $data = array_merge([
                $name => 'completed',
            ], $benchmark->result());

            $this->benchmarkCompleted($data);
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
     * @param mixed $name
     * @param mixed $benchmark
     * @return void
     */
    protected function benchmarkSkipped($name, $benchmark)
    {
        ++$this->statistics['skipped'];

        $data = [
            (string)$name => 'skipped',
        ];

        if ($this->isDebugMode()) {
            $debug = [];

            if (is_array($benchmark) && array_key_exists('fail', $benchmark)) {
                $debug['type'] = 'object';
                $debug['class'] = ucfirst($name);
                $debug['message'] = $benchmark['fail'];
            } else {
                $debug['type'] = gettype($benchmark);
                $debug['class'] = is_object($benchmark) ? get_class($benchmark) : 'not an object';
                $debug['message'] = 'Not a benchmark object.';
            }

            $data = array_merge($data, $debug);
        }

        $this->reporter->showBlock($data);
    }

    /**
     * @param array $message
     * @return void
     */
    protected function benchmarkCompleted(array $message)
    {
        ++$this->statistics['completed'];

        if ($this->hasCorrectExecutionTime($message)) {
            $this->statistics['total_time'] += $message['exec_time'];
        } else {
            $message['exec_time'] = 'malformed time';
        }

        if ($this->isSilentMode()) {
            $name = ($n = array_search('completed', $message, true)) ? $n : 'malformed name';
            $message = [$name => $message['exec_time']];
        }

        $this->reporter->showBlock($message);

        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $this->reporter->showSeparator();
        }
    }

    /**
     * @return bool
     */
    protected function isSilentMode()
    {
        return !$this->isDebugMode() && !$this->isVerboseMode();
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
    protected function hasBenchmarks()
    {
        return isset($this->benchmarks) && count($this->benchmarks) > 0;
    }

    /**
     * @return bool
     */
    protected function hasCompletedBenchmarks()
    {
        return isset($this->statistics['completed']) && ($this->statistics['completed'] > 0);
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
     * @param array $keys
     * @return array
     */
    public function getSummary(array $keys = [])
    {
        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $keys = array_merge(['started_at', 'stopped_at'], $keys);
        }

        return $this->getStatisticsForHumans($keys);
    }

    /**
     * @return array
     */
    public function getBenchmarksSummary()
    {
        if (!$this->hasBenchmarks()) {
            return ['skip' => 'no benchmarks were found'];
        }

        if ($this->isSilentMode()) {
            return [];
        }

        list($completed, $skipped) = array_values($this->getStatistics(['completed', 'skipped']));

        $summary = ['done' => $this->generatePluralizedBenchmarkCount($completed) . ' completed'];

        if ($this->isVerboseMode() || $this->isDebugMode() || $this->hasSkippedBenchmarks()) {
            $summary = array_merge($summary, ['skip' => $this->generatePluralizedBenchmarkCount($skipped) . ' skipped']);
        }

        return $summary;
    }

    /**
     * @param int $count
     * @return string
     */
    protected function generatePluralizedBenchmarkCount($count)
    {
        return ($count === 1) ? $count . ' benchmark' : $count . ' benchmarks';
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
    protected function getVersionString()
    {
        return $this->getFullTitle() . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $message = '';
        $message .= $this->getFullTitle() . str_repeat(PHP_EOL, 2);
        $message .= <<<EOT
Usage:
  benchmark [options]

Available Options:
  -l, --list                Prints the list of available benchmarks
  -b, --benchmarks <list>   Executes benchmarks from a comma separated list
  -i, --iterations <num>    Executes benchmarks with fixed number of iterations
  -h, --help                Prints this usage information and exits
  --version                 Prints the version and exits
  -v, --verbose             Prints verbose information during execution
  --debug                   Prints detailed information during execution
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
