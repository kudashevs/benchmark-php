<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Terminators;

class CliTerminator implements TerminatorInterface
{
    /**
     * @return void
     */
    public function terminateOnSuccess()
    {
        /*
         * The status 0 is used to terminate the program successfully.
         * https://www.php.net/manual/en/function.exit.php
        */
        $successCode = 0;

        $this->terminate($successCode);
    }

    /**
     * @param int $code Possible error code values are from 1 to 255.
     * @return void
     */
    public function terminateOnError($code = 1)
    {
        /*
         * Exit statuses should be in the range 0 to 254 (255 is reserved by PHP and shall not be used).
         * https://www.php.net/manual/en/function.exit.php
         */
        if ($code < 1 || $code > 254) {
            $code = 1;
        }

        $this->terminate($code);
    }

    protected function terminate($code = 0)
    {
        flush();
        ob_flush();

        exit($code);
    }
}