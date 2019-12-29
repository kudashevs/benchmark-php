<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Arguments;

use BenchmarkPHP\Application;
use BenchmarkPHP\Benchmarks\Benchmarks;
use BenchmarkPHP\Exceptions\EmptyArgumentException;
use BenchmarkPHP\Exceptions\WrongArgumentException;
use BenchmarkPHP\Exceptions\UnknownArgumentException;

class CliArgumentsHandler implements ArgumentsHandlerInterface
{
    /**
     * @var Benchmarks
     */
    private $benchmarks;

    public function __construct()
    {
        $this->benchmarks = new Benchmarks();
    }

    /**
     * Parses arguments and returns [$action, $options] array.
     *
     * @param array $data
     * @throws EmptyArgumentException|WrongArgumentException|UnknownArgumentException
     * @return array
     */
    public function parse(array $data)
    {
        $arguments = $this->initArguments($data);

        return $this->parseArguments($arguments);
    }

    /**
     * Checks for empty arguments and required values.
     *
     * @param array $arguments
     * @throws EmptyArgumentException|WrongArgumentException
     * @return array
     */
    protected function initArguments(array $arguments)
    {
        $result = [];

        if (empty($arguments)) {
            return $result;
        }

        while ($argument = current($arguments)) {
            $next = next($arguments);

            if (in_array($argument, Application::REQUIRE_VALUE_ARGUMENTS, true)) {
                $this->checkRequiredArgumentHasValue($argument, $next);
                $this->checkRequiredArgumentIsCorrect($argument, $next);
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
     * @throws EmptyArgumentException
     * @return void
     */
    private function checkRequiredArgumentHasValue($argument, $value)
    {
        if ($value === false) {
            throw new EmptyArgumentException('Option ' . $argument . ' requires some value. Empty value is passed.');
        }
    }

    /**
     * @param string $argument
     * @param mixed $value
     * @throws WrongArgumentException
     * @return void
     */
    private function checkRequiredArgumentIsCorrect($argument, $value)
    {
        if (strpos($value, '-') === 0) {
            throw new WrongArgumentException('Option ' . $argument . ' requires some value. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }
    }

    /**
     * Parses and validates arguments depending on they types and returns [$action, $options] array.
     *
     * @param array $arguments
     * @throws WrongArgumentException|UnknownArgumentException
     * @return array
     */
    protected function parseArguments(array $arguments)
    {
        if (empty($arguments)) {
            return ['default', []];
        }

        $action = 'default';
        $options = [];

        foreach ($arguments as $argument => $value) {
            switch ($argument) {
                case '-h':
                case '--help':
                    $action = 'help';

                    break;

                case '-a':
                case '--all':
                    $this->checkMutuallyExclusive($argument, $arguments);
                    $action = 'handle';
                    $options['benchmarks'] = $this->benchmarks->getBenchmarks(); // todo move get benchmarks to application

                    break;

                case '-e':
                case '--exclude':
                    $this->checkMutuallyInclusive($argument, $arguments);
                    $action = 'handle';
                    $options['excluded'] = $this->parseRequiredArgumentIsBenchmarkName($argument, $value); // todo move get benchmarks to application

                    break;

                case '-b':
                case '--benchmarks':
                    $action = 'handle';
                    $options['benchmarks'] = $this->parseRequiredArgumentIsBenchmarkName($argument, $value); // todo move get benchmarks to application

                    break;

                case '-l':
                case '--list':
                    $action = 'list'; // todo return list?

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
                    $action = 'version';

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
                    throw new UnknownArgumentException('Unknown option ' . $argument . '');

                    break;
            }
        }

        if (isset($options['benchmarks'], $options['excluded'])) { // todo refactor initialize first
            $options['benchmarks'] = array_diff_key($options['benchmarks'], $options['excluded']);
        }

        return ['action' => $action, 'options' => $options];
    }

    /**
     * @param string $key
     * @param array $arguments
     * @throws WrongArgumentException
     * @return void
     */
    private function checkMutuallyExclusive($key, array $arguments)
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

        throw new WrongArgumentException('Option ' . $key . ' is mutually exclusive with ' . $exclusive . '. Wrong arguments are passed.');
    }

    /**
     * @param string $key
     * @param array $arguments
     * @throws WrongArgumentException
     * @return void
     */
    private function checkMutuallyInclusive($key, array $arguments)
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

        throw new WrongArgumentException('Option ' . $key . ' is mutually inclusive with ' . $require . '. Wrong arguments are passed.');
    }

    /**
     * @param string $argument
     * @param string $value
     * @throws WrongArgumentException
     * @return array
     */
    private function parseRequiredArgumentIsBenchmarkName($argument, $value)
    {
        if (empty($value) || !is_string($value)) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a benchmark name. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        $this->checkRequiredArgumentIsCorrect($argument, $value);

        $benchmarks = explode(',', $value);

        if (!empty($unknown = array_diff($benchmarks, $this->benchmarks->getBenchmarksNames()))) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a valid benchmark name or list of names. Check ' . $this->generatePrintableWithSpace(implode(
                ',',
                $unknown
                )) . 'or use -l for more information.');
        }

        return array_flip($benchmarks);
    }

    /**
     * @param string $argument
     * @param int|float $value
     * @throws WrongArgumentException
     * @return int
     */
    private function parseRequiredArgumentIsIteration($argument, $value)
    {
        $minIterations = 1;
        $maxIterations = 100000000;

        if ($value === '' || !is_numeric($value)) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a number of iterations. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        $iterations = (int)$value;

        if ($iterations < $minIterations || $iterations > $maxIterations) {
            throw new WrongArgumentException('Option ' . $argument . ' requires the value between ' . $minIterations . ' and ' . $maxIterations . '. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        return $iterations;
    }

    /**
     * @param string $argument
     * @param int|float $value
     * @throws WrongArgumentException
     * @return int
     */
    private function parseRequiredArgumentIsPositiveInteger($argument, $value)
    {
        if ($value === '' || !is_numeric($value)) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a numeric value. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        $value = (int)$value;

        if ($value < 0) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a positive numeric. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        return $value;
    }

    /**
     * @param string $argument
     * @param string $value
     * @throws WrongArgumentException
     * @return string
     */
    private function parseRequiredArgumentIsFilename($argument, $value)
    {
        if (empty($value) || !is_string($value)) {
            throw new WrongArgumentException('Option ' . $argument . ' requires a filename. Empty or wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.');
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function generatePrintableWithSpace($value)
    {
        return $this->generatePrintable($value) . ' ';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function generatePrintable($value)
    {
        return is_scalar($value) ? (string)$value : '';
    }
}
