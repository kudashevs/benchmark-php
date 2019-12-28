<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Input;

class CliInput implements InputInterface
{
    /**
     * @var array
     */
    private $arguments;

    public function __construct()
    {
        /*
         * The first argument is always the name that was used to run the script.
         * We don't want it that's why we remove the first element from the array.
         */
        array_shift($_SERVER['argv']);

        $this->arguments = $_SERVER['argv'];
    }

    /**
     * @return array
     */
    public function arguments()
    {
        return $this->arguments;
    }
}
