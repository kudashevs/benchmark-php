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
use BenchmarkPHP\Exceptions\ValidationException;

class CliHandler implements ArgumentsHandlerInterface
{
    public function validate(array $data)
    {
        $arguments = $this->initArguments($data);

        return $arguments;
    }

    /**
     * @param array $arguments
     * @return array
     * @throws ValidationException
     */
    protected function initArguments(array $arguments)
    {
        $result = [];

        if (empty($arguments)) {
            return $result; // todo ['help action', options]
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
     * @throws ValidationException
     * @return void
     */
    protected function checkRequiredArgumentHasValue($argument, $value)
    {
        if ($value === false) {
            throw new ValidationException('Option ' . $argument . ' requires some value. Empty value is passed.' . PHP_EOL);
        }
    }

    /**
     * @param string $argument
     * @param mixed $value
     * @throws ValidationException
     * @return void
     */
    protected function checkRequiredArgumentIsCorrect($argument, $value)
    {
        if (strpos($value, '-') === 0) {
            throw new ValidationException('Option ' . $argument . ' requires some value. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }
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
}
