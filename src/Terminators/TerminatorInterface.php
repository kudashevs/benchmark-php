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

interface TerminatorInterface
{
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