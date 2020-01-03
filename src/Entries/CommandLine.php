<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Entries;

use BenchmarkPHP\Application;
use BenchmarkPHP\Input\CliInput;
use BenchmarkPHP\Output\CliOutput;

class CommandLine
{
    public static function run()
    {
        $app = new Application(new CliInput(), new CliOutput());
        $app->run();
    }
}
