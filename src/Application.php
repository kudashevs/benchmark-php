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
use BenchmarkPHP\Traits\PluralizeTrait;
use BenchmarkPHP\Traits\VerbosityTrait;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Presenters\PresenterFactory;
use BenchmarkPHP\Presenters\PresenterInterface;
use BenchmarkPHP\Arguments\ArgumentsHandlerFactory;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class Application
{
    use VerbosityTrait, PluralizeTrait;

    /**
     * @var string
     */
    const NAME = 'Benchmark PHP';

    /**
     * @var string
     */
    const VERSION = '2.0.0';

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

        $this->informer = new Informer();
        $this->repository = new Benchmarks();
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
        $list = $this->repository->getNames();

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
        $benchmarks = $this->repository->getInstances($this->options);

        $this->presenter->header($this->getFullVersion());
        $this->presenter->block($this->informer->getSystemInformation());
        $this->presenter->separator();

        $this->handleBenchmarks($benchmarks);

        if (!$this->isSilentMode()) {
            $this->presenter->separator();
            $this->presenter->block($this->getBenchmarksSummary());
        }

        $this->presenter->footer($this->getExecutionSummary(['total_time']));

        $this->presenter->onSuccess();
    }

    /**
     * Handles benchmarks collection and reports results.
     *
     * @param array $benchmarks
     * @return void
     */
    private function handleBenchmarks(array $benchmarks)
    {
        $this->beforeHandle(); // turn off cache, gc, etc.

        /** @var AbstractBenchmark|array $benchmark */
        foreach ($benchmarks as $name => $benchmark) {
            if (!$this->isValidBenchmark($benchmark)) {
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
    private function beforeHandle()
    {
        $this->statistics['started_at'] = date(self::DATE_FORMAT);
    }

    /**
     * @return void
     */
    private function afterHandle()
    {
        $this->statistics['stopped_at'] = date(self::DATE_FORMAT);
    }

    /**
     * @param object $benchmark
     * @return bool
     */
    private function isValidBenchmark($benchmark)
    {
        return is_object($benchmark) && $benchmark instanceof AbstractBenchmark;
    }

    /**
     * Accumulates data about skipped benchmark and reports it.
     *
     * @param mixed $name
     * @param mixed $benchmark
     * @return void
     */
    private function benchmarkSkipped($name, $benchmark)
    {
        ++$this->statistics['skipped'];

        $name = (string)$name;

        $report = $this->generateSkippedReport($name, $benchmark);

        $this->presenter->block($report);
    }

    /**
     * @param string $name
     * @param mixed $benchmark
     * @return array
     */
    private function generateSkippedReport($name, $benchmark)
    {
        if (!$this->isSilentMode()) {
            return $this->generateSkippedVerboseReport($name, $benchmark);
        }

        return $this->generateSkippedShortReport($name);
    }

    /**
     * @param string $name
     * @param mixed $benchmark
     * @return array
     */
    private function generateSkippedVerboseReport($name, $benchmark)
    {
        if ($this->hasSkipInformation($benchmark)) {
            return $this->generateSkippedReportWithSkipInformation($name, $benchmark);
        }

        return $this->generateSkippedReportWithoutSkipInformation($name, $benchmark);
    }

    /**
     * @param mixed $benchmark
     * @return bool
     */
    private function hasSkipInformation($benchmark)
    {
        return is_array($benchmark) && array_key_exists('fail', $benchmark) && array_key_exists('message', $benchmark);
    }

    /**
     * @param string $name
     * @param mixed $benchmark
     * @return array
     */
    private function generateSkippedReportWithSkipInformation($name, $benchmark)
    {
        $information[$name] = 'skipped';

        if ($this->isDebugMode()) {
            $information['stage'] = $benchmark['fail'];
            $information['type'] = 'object';
            $information['class'] = $name;
        }

        $information['message'] = $benchmark['message'];

        return $information;
    }

    /**
     * @param string $name
     * @param mixed $benchmark
     * @return array
     */
    private function generateSkippedReportWithoutSkipInformation($name, $benchmark)
    {
        $information[$name] = 'skipped';

        if ($this->isDebugMode()) {
            $information['stage'] = 'unknown';
            $information['type'] = gettype($benchmark);
            $information['class'] = is_object($benchmark) ? get_class($benchmark) : 'not an object';
        }

        $information['message'] = 'Incorrectly processed data. Check data source.';

        return $information;
    }

    /**
     * @param $name
     * @return array
     */
    private function generateSkippedShortReport($name)
    {
        return [
            $name => 'skipped',
        ];
    }

    /**
     * Accumulates data about completed benchmark and reports it.
     *
     * @param string $name
     * @param array $statistics
     * @return void
     */
    private function benchmarkCompleted($name, array $statistics)
    {
        ++$this->statistics['completed'];

        if ($this->hasValidExecutionTime($statistics)) {
            $this->statistics['total_time'] += $statistics['exec_time'];
        } else {
            $statistics['exec_time'] = 'malformed time';
        }

        $report = $this->generateCompletedReport($name, $statistics);

        $this->presenter->block($report);
    }

    /**
     * @param array $information
     * @return bool
     */
    private function hasValidExecutionTime(array $information)
    {
        return isset($information['exec_time']) && is_numeric($information['exec_time']);
    }

    /**
     * @param string $name
     * @param array $statistics
     * @return array
     */
    private function generateCompletedReport($name, $statistics)
    {
        if (!$this->isSilentMode()) {
            return $this->generateCompletedVerboseReport($name, $statistics);
        }

        return $this->generateCompletedShortReport($name, $statistics);
    }

    /**
     * @param $name
     * @param array $statistics
     * @return array
     */
    private function generateCompletedVerboseReport($name, array $statistics)
    {
        $data[$name] = 'completed';
        $data = array_replace($data, $statistics);

        return $data;
    }

    /**
     * @param string $name
     * @param array $statistics
     * @return array
     */
    private function generateCompletedShortReport($name, array $statistics)
    {
        $statistics = $this->formatExecutionTimeBatch($statistics);

        $allowedKeys = ['read_time', 'read_speed', 'write_time', 'write_speed'];
        $allowedInfo = array_intersect_key($statistics, array_flip($allowedKeys));

        $report = [
            $name => $statistics['exec_time'],
        ];

        $report = array_merge($report, $allowedInfo);

        return $report;
    }

    /**
     * @param array $data
     * @return array
     */
    private function formatExecutionTimeBatch(array $data)
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
    private function formatExecutionTime($time, $precision = null)
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
    private function isValidPrecision($precision)
    {
        if (!is_int($precision)) {
            return false;
        }

        return $precision >= 0 && $precision <= 12;
    }

    /**
     * @return bool
     */
    private function hasBenchmarks()
    {
        return $this->hasCompletedBenchmarks() || $this->hasSkippedBenchmarks();
    }

    /**
     * @return bool
     */
    private function hasCompletedBenchmarks()
    {
        return $this->statistics['completed'] > 0;
    }

    /**
     * @return bool
     */
    private function hasSkippedBenchmarks()
    {
        return $this->statistics['skipped'] > 0;
    }

    /**
     * @return array
     */
    private function getBenchmarksSummary()
    {
        if (!$this->hasBenchmarks()) {
            return ['skip' => 'no benchmarks were executed'];
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
    private function getExecutionSummary(array $keys = [])
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
        if (empty($keys)) {
            return $keys;
        }

        $result = $this->getStatistics($keys);

        if ($this->isSilentMode()) {
            $result = $this->formatExecutionTimeBatch($result);
        }

        $formatted = [];

        foreach ($result as $k => $v) {
            $newKey = ucfirst(str_replace('_', ' ', $k));
            $formatted[$newKey] = $v;
        }

        return $formatted;
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
    private function getHelp()
    {
        $message = '';
        $message .= <<<EOT
Usage:
  benchmark-php [options]

Benchmarking Options:

  -a, --all                 Executes all available benchmarks
  -e, --exclude <list>      Exclude benchmarks (is used only with -a option)
  -b, --benchmarks <list>   Executes benchmarks from a comma separated list
  -l, --list                Prints the list of available benchmarks
  -i, --iterations <num>    Executes benchmarks with fixed number of iterations
  -v, --verbose             Prints verbose information during execution
  --debug                   Prints detailed information during execution

Formatting Options:

  --time-precision <num>    Use precision for time formatting (min 1, max 12, default 3)

Miscellaneous Options:

  -h, --help                Prints this usage information and exits
  --version                 Prints the version and exits

Additional Options [filesystem benchmark only]:

  --temporary-file <file>   Path to specific file for filesystem benchmarking
  --decimal-prefix          Use decimal prefix kilo denotes 1000 (the default)
  --binary-prefix           Use binary prefix kilo denotes 1024
  --data-precision <num>    Use precision for data formatting (min 1, max 3, default 3)
  --disable-rounding        Disable rounding for data formatting
EOT;

        return $message;
    }
}
