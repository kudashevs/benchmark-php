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
     * @param string $data
     * @return mixed
     */
    public function write($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function writeln($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function error($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function errorln($data);

    /**
     * @return void
     */
    public function terminateOnSuccess();

    /**
     * @param int $code
     * @return void
     */
    public function terminateOnError($code);
}
