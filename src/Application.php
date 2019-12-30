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
use BenchmarkPHP\Input\InputInterface;
use BenchmarkPHP\Benchmarks\Benchmarks;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Presenters\PresenterFactory;
use BenchmarkPHP\Presenters\PresenterInterface;
use BenchmarkPHP\Arguments\ArgumentsHandlerFactory;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class Application
{
    /**
     * @var string
     */
    const NAME = 'Benchmark PHP';

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
     * @var string
     */
    private $action = 'default';

    /**
     * @var array
     */
    private $options = [
        'debug' => false,
        'verbose' => false,
    ];

    /**
     * @var PresenterInterface
     */
    private $presenter;

    /**
     * @var Informer
     */
    private $informer;

    /**
     * @var Benchmarks
     */
    private $repository;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->presenter = $this->createPresenter($output);

        $this->parseArguments($input);
        $this->buildInternals();

        $this->benchmarks = $this->initBenchmarks($this->options); // todo move to action

        // $this->run();
    }

    /**
     * @param OutputInterface $output
     * @return PresenterInterface
     */
    private function createPresenter(OutputInterface $output)
    {
        try {
            $presenter = (new PresenterFactory())->create($output);
        } catch (\Exception $e) {
            $output->write($e->getMessage());

            $output->terminateOnError(2);
        }

        return $presenter;
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    private function parseArguments(InputInterface $input)
    {
        $arguments = $input->arguments();

        try {
            $handler = (new ArgumentsHandlerFactory())->create($input);
            /**
             * Here we expect to get parsed arguments in form of a associative array.
             * The format should be ['action' => 'some_action', 'options' => [options]].
             */
            $initial = $handler->parse($arguments);
        } catch (\Exception $e) {
            $this->presenter->version($this->getFullVersion()); // todo separate method
            $this->presenter->block($e->getMessage());

            $this->presenter->onError(3); // todo error code from exception
        }

        $this->action = empty($initial['action']) ? $this->action : $initial['action'];
        $this->options = empty($initial['options']) ? $this->options : $initial['options'];
    }

    /**
     * @return void
     */
    private function buildInternals()
    {
        $this->informer = new Informer();
        $this->repository = new Benchmarks();
    }

    /**
     * Analyze action and invoke action.
     *
     * @return void
     */
    public function run() // refactor
    {
        switch ($this->action) {
            case 'default':
            case 'help':
                $this->runHelp();

                break;

            case 'handle':
                $this->runHandle();

                break;

            case 'list':
                $this->runList();

                break;

            case 'version':
                $this->runVersion();

                break;

            default:
                // should never happen
                $this->presenter->block('Action ' . $this->action . ' was not found. Please report it on github.');

                $this->presenter->onError(1);

                break;
        }
    }

    /**
     * @return void
     */
    private function runHelp()
    {
        $this->presenter->version($this->getFullVersion());
        $this->presenter->block($this->getHelp());

        $this->presenter->onSuccess();
    }

    /**
     * @return void
     */
    private function runList()
    {
        $list = $this->repository->getBenchmarksNames();

        $this->presenter->version($this->getFullVersion());
        $this->presenter->block('Available ' . $this->generatePluralized(count($list), 'benchmark'));
        $this->presenter->listing($list);

        $this->presenter->onSuccess();
    }

    /**
     * @return void
     */
    private function runVersion()
    {
        $this->presenter->block($this->getFullVersion());

        $this->presenter->onSuccess();
    }

    /**
     * @return void
     */
    private function runHandle()
    {
        $benchmarks = $this->repository->getInstantiated($this->options);

        $this->presenter->header($this->getFullVersion());
        $this->presenter->block($this->informer->getSystemInformation());
        $this->presenter->separator();

        $this->handleBenchmarks($benchmarks);

        $this->presenter->block($this->getBenchmarksSummary());
        $this->presenter->footer($this->getExecutionSummary(['total_time']));

        $this->presenter->onSuccess();
    }

    /**
     * @param array $options
     * @return array
     */
    protected function initBenchmarks(array $options = [])
    {
        return $this->repository->getInstantiated($options);
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

        $this->presenter->block($data);

        if (!$this->isSilentMode()) {
            $this->presenter->separator();
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

        $this->presenter->block($data);

        if (!$this->isSilentMode()) {
            $this->presenter->separator();
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
        return $this->hasCompletedBenchmarks() || $this->hasSkippedBenchmarks();
    }

    /**
     * @return bool
     */
    protected function hasCompletedBenchmarks()
    {
        return $this->statistics['completed'] > 0;
    }

    /**
     * @return bool
     */
    protected function hasSkippedBenchmarks()
    {
        return $this->statistics['skipped'] > 0;
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
            return ['skip' => 'no benchmarks were executed'];
        }

        if ($this->isSilentMode()) {
            return [];
        }

        list($completed, $skipped) = array_values($this->getStatistics(['completed', 'skipped']));

        $summary = ['done' => $this->generatePluralized($completed, 'benchmark') . ' completed'];

        if ($this->isVerboseMode() || $this->isDebugMode() || $this->hasSkippedBenchmarks()) {
            $summary = array_merge($summary, ['skip' => $this->generatePluralized($skipped, 'benchmark') . ' skipped']);
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
     * @param string $text
     * @return string
     */
    protected function generatePluralized($count, $text)
    {
        $text = trim($text);

        return ($count === 1) ? $count . ' ' . $text : $count . ' ' . $text . 's';  // todo add -es for -s ending words
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    public function getFullVersion()
    {
        return self::NAME . ' ' . $this->getVersion();
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $message = '';
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
}
