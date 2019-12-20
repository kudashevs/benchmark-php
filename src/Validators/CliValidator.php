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

use BenchmarkPHP\Benchmark;

class CliValidator implements ValidatorInterface
{
    public function validate($data)
    {
        return $this->initArguments($data);
    }

    /**
     * @param array $arguments
     * @return array
     */
    protected function initArguments(array $arguments)
    {
        /*
         * todo remove (we suppose to get clean arguments array)
         * The first argument is always the name that was used to run the script.
         * We don't want it that's why we remove the first element from the array.
         */
        array_shift($arguments);

        $result = [];

        if (empty($arguments)) {
            return $result;
        }

        while ($argument = current($arguments)) {
            $next = next($arguments);

            if (in_array($argument, Benchmark::REQUIRE_VALUE_ARGUMENTS, true)) {
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
            //$this->reporter->showBlock($this->getVersionString()); // todo update
            $this->terminateWithMessage('Option ' . $argument . ' requires some value. Empty value is passed.' . PHP_EOL);
        }
    }

    /**
     * @param string $argument
     * @param mixed $value
     * @return void
     */
    protected function checkRequiredArgumentNotAnOption($argument, $value)
    {
        if (strpos($value, '-') === 0) {
            //$this->reporter->showBlock($this->getVersionString()); // todo update
            $this->terminateWithMessage('Option ' . $argument . ' requires some value. Wrong value ' . $this->generatePrintableWithSpace($value) . 'is passed.' . PHP_EOL);
        }
    }

    // todo move to responsibility

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
