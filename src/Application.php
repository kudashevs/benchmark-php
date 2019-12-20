<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP;

use BenchmarkPHP\Informers\Informer;
use BenchmarkPHP\Benchmarks\Benchmarks;
use BenchmarkPHP\Validators\CliValidator;
use BenchmarkPHP\Reporters\ReporterInterface;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class Application
{
    /**
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var int Execution time output precision (possible values are from 1 to 12).
     */
    const TIME_PRECISION = 3;

    /**
     * @var array
     */
    const REQUIRE_VALUE_ARGUMENTS = [
        '-e',
        '--exclude',
        '-b',
        '--benchmarks',
        '-i',
        '--iterations',
        '--temporary-file',
        '--time-precision',
        '--data-precision',
    ];

    /**
     * @var ReporterInterface
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
     * @param array $arguments
     * @param ReporterInterface $reporter
     */
    public function __construct(array $arguments, ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
        $arguments = (new CliValidator([]))->validate($arguments);
        $this->options = $this->parseArguments($arguments);
        $this->benchmarks = $this->initBenchmarks();
    }

    private function init()
    {
        // init function?
        // set reporter
        // try/catch get action, options
        // get informer
        // get benchmark
    }

    /**
     * Aggregate and print data.
     *
     * @return void
     */
    public function run()
    {
        $this->reporter->showHeader($this->getFullTitle());

        $this->reporter->showBlock((new Informer())->getSystemInformation());
        $this->reporter->showSeparator();

        $this->handleBenchmarks();

        $this->reporter->showBlock($this->getBenchmarksSummary());
        $this->reporter->showFooter($this->getExecutionSummary(['total_time']));
    }

    /**
     * @param array $arguments
     * @return array Return array of options
     */
    protected function parseArguments(array $arguments)
    {
        if (empty($arguments)) {
            $this->reporter->showBlock($this->getHelp());
            $this->terminateWithCode(0);
        }

        $options = [];

        foreach ($arguments as $argument => $value) {
            switch ($argument) {
                case '-h':
                case '--help':
                    $this->reporter->showBlock($this->getHelp());
                    $this->terminateWithCode(0);

                    break;

                case '-a':
                case '--all':
                    $this->checkMutuallyExclusive($argument, $arguments);
                    $options['benchmarks'] = Benchmarks::BENCHMARKS;

                    break;

                case '-e':
                case '--exclude':
                    $this->checkMutuallyInclusive($argument, $arguments);
                    $options['excluded'] = $this->parseRequiredArgumentIsBenchmarkName($argument, $value);

                    break;

                case '-b':
                case '--benchmarks':
                    $options['benchmarks'] = $this->parseRequiredArgumentIsBenchmarkName($argument, $value);

                    break;

                case '-l':
                case '--list':
                    $this->reporter->showBlock($this->getVersionString());
                    $this->reporter->showBlock($this->listBenchmarks('header'), 'list');
                    $this->terminateWithCode(0);

                    break;

                case '-i':
                case '--iterations':
                    $options['iterations'] = $this->parseRequiredArgumentIsIteration($argument, $value);

                    break;

                case '--time-precision':
                    $options['time_precise'] = $this->parseRequiredArgumentIsPositiveInteger($argument, $value);

                    break;

                case '-v':
                case '--verbose':
                    $options['verbose'] = true;

                    break;

                case '--debug':
                    $options['debug'] = true;

                    break;

                case '--version':
                    $this->reporter->showBlock($this->getVersionString());
                    $this->terminateWithCode(0);

                    break;

                case '--decimal-prefix':
                    $options['prefix'] = 'decimal';

                    break;

                case '--binary-prefix':
                    $options['prefix'] = 'binary';

                    break;

                case '--data-precision':
                    $options['data_precise'] = $this->parseRequiredArgumentIsPositiveInteger($argument, $value);

                    break;

                case '--disable-rounding':
                    $options['rounding'] = false;

                    break;

                case '--temporary-file':
                    $options['file'] = $this->parseRequiredArgumentIsFilename($argument, $value);

                    break;

                default:
                    $this->reporter->showBlock($this->getVersionString());
                    $this->terminateWithMessage('Unknown option ' . $argument . PHP_EOL);

                    break;
            }
        }

        if (isset($options['benchmarks'], $options['excluded'])) {
            $options['benchmarks'] = array_diff_key($options['benchmarks'], $options['excluded']);
        }

        return $options;
    }

    /**
     * @param $key
     * @param array $arguments
     * @return void
     */
    protected function checkMutuallyInclusive($key, array $arguments)
    {
        $include = [
            '-e' => ['-a', '--all'],
            '--exclude' => ['-a', '--all'],
        ];

        if (!array_key_exists($key, $include)) {
            return;
        }

        if (array_intersect_key($arguments, array_flip($include[$key]))) {
            return;
        }

        $require = implode(' or ', $include[$key]);

        $this->reporter->showBlock($this->getVersionString());
        $this->terminateWithMessage('Option ' . $key . ' is mutually inclusive with ' . $require . '. Wrong arguments are passed.' . PHP_EOL);
    }

    /**
     * @param string $key
     * @param array $arguments
     * @return void
     */
    protected function checkMutuallyExclusive($key, array $arguments)
    {
        $exclude = [
            '-a' => ['-b', '--benchmarks'],
            '--all' => ['-b', '--benchmarks'],
        ];

        if (!array_key_exists($key, $exclude)) {
            return;
        }

        if (!$exclusive = array_intersect_key($arguments, array_flip($exclude[$key]))) {
            return;
        }

        $exclusive = implode(',', array_keys($exclusive));

        $this->reporter->showBlock($this->getVersionString());
        $this->terminateWithMessage('Option ' . $key . ' is mutually exclusive with ' . $exclusive . '. Wrong arguments are passed.' . PHP_EOL);
    }

    /**
     * @param string $argument
     * @param string $value
     * @return array
     */
    protected function parseRequiredArgumentIsBenchmarkName($argument, $value)
    {
        if (empty($value) || !is_string($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a benchmark name. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $this->checkRequiredArgumentNotAnOption($argument, $value);

        $benchmarks = explode(',', $value);

        if (!empty($unknown = array_diff($benchmarks, array_flip(Benchmarks::BENCHMARKS)))) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a valid benchmark name or list of names. Check ' . $this->generatePrintableWithSpace(implode(
                ',',
                $unknown
            )) . 'or use -l for more information.' . PHP_EOL);
        }

        return array_flip($benchmarks);
    }

    /**
     * @param string $argument
     * @param mixed $value
     * @return void
     */
    protected function checkRequiredArgumentNotAnOption($argument, $value) // remove
    {
        if (strpos($value, '-') === 0) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires some value. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }
    }

    /**
     * @param string $argument
     * @param int|float $value
     * @return int
     */
    protected function parseRequiredArgumentIsIteration($argument, $value)
    {
        $minIterations = 1;
        $maxIterations = 100000000;

        if ($value === '' || !is_numeric($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a number of iterations. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $iterations = (int)$value;

        if ($iterations < $minIterations || $iterations > $maxIterations) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires the value between ' . $minIterations . ' and ' . $maxIterations . '. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        return $iterations;
    }

    /**
     * @param string $argument
     * @param int|float $value
     * @return int
     */
    protected function parseRequiredArgumentIsPositiveInteger($argument, $value)
    {
        if ($value === '' || !is_numeric($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a numeric value. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $value = (int)$value;

        if ($value < 0) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a positive numeric. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        return $value;
    }

    /**
     * @param string $argument
     * @param string $value
     * @return string
     */
    protected function parseRequiredArgumentIsFilename($argument, $value)
    {
        if (empty($value) || !is_string($value)) {
            $this->reporter->showBlock($this->getVersionString());
            $this->terminateWithMessage('Option ' . $argument . ' requires a filename. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }

        $this->checkRequiredArgumentNotAnOption($argument, $value);

        return $value;
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
        //$initialized = !empty($this->options['benchmarks']) ? array_intersect_key(self::BENCHMARKS, $this->options['benchmarks']) : self::BENCHMARKS; // todo realize partial get

        return (new Benchmarks())->getInstantiated($this->options);
    }

    /**
     * @param string $style
     * @return array
     */
    protected function listBenchmarks($style = '')
    {
        $benchmarks = array_keys(self::BENCHMARKS);

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
            if (!is_object($benchmark) || !$benchmark instanceof AbstractBenchmark) { // todo: move logic inside
                $this->benchmarkSkipped($name, $benchmark);

                continue;
            }

            try {
                $benchmark->before();

                $benchmark->handle();

                $benchmark->after();

                $results = $benchmark->result();
            } catch (\Exception $e) {
                $this->benchmarkSkipped($name, [
                    'fail' => 'runtime',
                    'message' => $e->getMessage(),
                ]);

                continue;
            }

            $this->benchmarkCompleted($name, $results);
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

        $name = (string)$name;

        $data = [
            $name => 'skipped',
        ];

        if ($this->isVerboseMode()) {
            $verbose = [];

            if ($this->hasSkipInformation($benchmark)) {
                $verbose['message'] = $benchmark['message'];
            } else {
                $verbose['message'] = 'Why it wasn\'t a benchmark object?';
            }

            $data = array_merge($data, $verbose);
        }

        if ($this->isDebugMode()) {
            $debug = [];

            if ($this->hasSkipInformation($benchmark)) {
                $debug['status'] = $benchmark['fail'];
                $debug['type'] = 'object';
                $debug['class'] = $name;
                $debug['message'] = $benchmark['message'];
            } else {
                $debug['status'] = 'unknown';
                $debug['type'] = gettype($benchmark);
                $debug['class'] = is_object($benchmark) ? get_class($benchmark) : 'not an object';
                $debug['message'] = 'Why it wasn\'t a benchmark object?';
            }

            $data = array_merge($data, $debug);
        }

        $this->reporter->showBlock($data);

        if (!$this->isSilentMode()) {
            $this->reporter->showSeparator();
        }
    }

    /**
     * @param $benchmark
     * @return bool
     */
    protected function hasSkipInformation($benchmark)
    {
        return is_array($benchmark) && array_key_exists('fail', $benchmark) && array_key_exists('message', $benchmark);
    }

    /**
     * @param string $name
     * @param array $statistics
     * @return void
     */
    protected function benchmarkCompleted($name, array $statistics)
    {
        ++$this->statistics['completed'];

        if ($this->hasValidExecutionTime($statistics)) {
            $this->statistics['total_time'] += $statistics['exec_time'];
        } else {
            $statistics['exec_time'] = 'malformed time';
        }

        $data = $this->generateDefaultReport($name, $statistics);

        if (!$this->isSilentMode()) {
            $data[$name] = 'completed';
            $data = array_replace($data, $statistics);
        }

        $this->reporter->showBlock($data);

        if (!$this->isSilentMode()) {
            $this->reporter->showSeparator();
        }
    }

    /**
     * @param array $information
     * @return bool
     */
    protected function hasValidExecutionTime(array $information)
    {
        return isset($information['exec_time']) && is_numeric($information['exec_time']);
    }

    /**
     * @param string $name
     * @param array $statistics
     * @return array
     */
    protected function generateDefaultReport($name, array $statistics)
    {
        $statistics = $this->formatExecutionTimeBatch($statistics);

        $additionalKeys = ['read_time', 'read_speed', 'write_time', 'write_speed'];
        $additionalInformation = array_intersect_key($statistics, array_flip($additionalKeys));

        $report = [
            $name => $this->formatExecutionTime($statistics['exec_time']),
        ];

        $report = array_merge($report, $additionalInformation);

        return $report;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatExecutionTimeBatch(array $data)
    {
        foreach ($data as $k => $v) {
            if (substr($k, -5) === '_time') {
                $data[$k] = $this->formatExecutionTime($v);
            }
        }

        return $data;
    }

    /**
     * @param mixed $time
     * @param int $precision
     * @return string
     */
    protected function formatExecutionTime($time, $precision = null)
    {
        if (!is_numeric($time)) {
            return $time;
        }

        $precision = $this->isValidPrecision($precision) ? $precision : self::TIME_PRECISION;

        if (isset($this->options['time_precise']) && $this->isValidPrecision($this->options['time_precise'])) {
            $precision = $this->options['time_precise'];
        }

        if ($precision === 0) {
            return floor($time) . 's';
        }

        /*
         * We don't want to round the last two digits as number_format does, so we increase
         * precision by two and then we will cut the last two digits in the output.
         */
        $time = number_format($time, $precision + 2, '.', '');

        return substr($time, 0, -2) . 's';
    }

    /**
     * @param mixed $precision
     * @return bool
     */
    protected function isValidPrecision($precision)
    {
        if (!is_int($precision)) {
            return false;
        }

        return $precision >= 0 && $precision <= 12;
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
     * @param array $keys
     * @return array
     */
    public function getExecutionSummary(array $keys = [])
    {
        if (!$this->isSilentMode()) {
            $keys = array_merge(['started_at', 'stopped_at'], $keys);
        }

        return $this->getStatisticsForHumans($keys);
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

        if ($this->isSilentMode() && array_key_exists('total_time', $result)) {
            $result['total_time'] = $this->formatExecutionTime($result['total_time']);
        }

        $updated = [];

        foreach ($result as $k => $v) {
            $newKey = ucfirst(str_replace('_', ' ', $k));
            $updated[$newKey] = $v;
        }

        return $updated;
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
  benchmark-php [options]

Available Options:
  -h, --help                Prints this usage information and exits
  -a, --all                 Executes all available benchmarks
  -e, --exclude <list>      Exclude benchmarks (is used only with -a option)
  -b, --benchmarks <list>   Executes benchmarks from a comma separated list
  -l, --list                Prints the list of available benchmarks
  -i, --iterations <num>    Executes benchmarks with fixed number of iterations
  -v, --verbose             Prints verbose information during execution
  --debug                   Prints detailed information during execution
  --version                 Prints the version and exits

Additional Options [filesystem]:
  --temporary-file <file>   Path to specific file for filesystem benchmarking
  --decimal-prefix          Use decimal prefix kilo denotes 1000 (the default)
  --binary-prefix           Use binary prefix kilo denotes 1024
  --data-precision <num>    Use precision for data formatting (min 1, max 3, default 3)
  --disable-rounding        Disable rounding for data formatting
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
}
