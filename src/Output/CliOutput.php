<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Output;

use BenchmarkPHP\Exceptions\RuntimeException;

class CliOutput implements OutputInterface
{
    /**
     * @var resource
     */
    const STANDARD_OUTPUT = STDOUT;

    /**
     * @var resource
     */
    const STANDARD_ERROR = STDERR;

    /**
     * @param string $data
     * @return void
     */
    public function write($data)
    {
        $this->writeRaw(self::STANDARD_OUTPUT, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function error($data)
    {
        $this->writeRaw(self::STANDARD_ERROR, $data);
    }

    /**
     * @param resource $resource
     * @param string $data
     */
    protected function writeRaw($resource, $data)
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Unable to write to a non resource. Check arguments.');
        }

        if (!is_scalar($data)) {
            throw new RuntimeException('Unable to write a non string data. Check arguments.');
        }

        fwrite($resource, $data);
    }

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

    /**
     * @param int $code
     * @return void
     */
    protected function terminate($code = 0)
    {
        fflush(self::STANDARD_OUTPUT);

        exit($code);
    }
}
