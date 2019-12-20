<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Validators;

use BenchmarkPHP\Application;
use BenchmarkPHP\Exceptions\ValidationException;

class CliValidator implements ValidatorInterface
{
    /**
     * @param array $benchmarks
     */
    public function __construct(array $benchmarks)
    {
    }

    public function validate(array $data)
    {
        return $this->initArguments($data);
    }

    /**
     * @param array $arguments
     * @return array
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
