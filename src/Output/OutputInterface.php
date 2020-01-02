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

interface OutputInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function write($data);

    /**
     * @param mixed $data
     * @return mixed
     */
    public function error($data);

    /**
     * Makes some cleanup and terminates script after successful execution.
     *
     * @return void
     */
    public function terminateOnSuccess();

    /**
     * Makes some cleanup and terminates script after critical error.
     *
     * @param int $code
     * @return void
     */
    public function terminateOnError($code);
}
