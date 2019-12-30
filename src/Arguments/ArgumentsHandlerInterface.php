<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Arguments;

interface ArgumentsHandlerInterface
{
    /**
     * @var array
     */
    const REQUIRE_VALUE_ARGUMENTS = [
        '-e',
        '--exclude',
        '-b',
        '--benchmarks',
        '-i',
        '--iterations',
        '--temporary-file',
        '--time-precision',
        '--data-precision',
    ];

    /**
     * @param array $data
     * @return mixed
     */
    public function parse(array $data);
}
