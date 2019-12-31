<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Integration\Presenters;

use BenchmarkPHP\Output\OutputInterface;

class DummyOutput implements OutputInterface
{
    public function write($data)
    {
        echo $data;
    }

    public function error($data)
    {
        echo $data;
    }

    public function terminateOnSuccess()
    {
        return null;
    }

    public function terminateOnError($code)
    {
        return null;
    }
}
