<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks;

use BenchmarkPHP\Benchmarks\Benchmarks\Arrays;
use BenchmarkPHP\Benchmarks\Benchmarks\Floats;
use BenchmarkPHP\Benchmarks\Benchmarks\Objects;
use BenchmarkPHP\Benchmarks\Benchmarks\Strings;
use BenchmarkPHP\Benchmarks\Benchmarks\Integers;
use BenchmarkPHP\Benchmarks\Benchmarks\Filesystem;

class Benchmarks
{
    /**
     * @var array
     */
    const BENCHMARKS = [ // 'bools', 'files', 'database', 'network'
        'integers' => Integers::class,
        'floats' => Floats::class,
        'strings' => Strings::class,
        'arrays' => Arrays::class,
        'objects' => Objects::class,
        'filesystem' => Filesystem::class,
    ];

    /**
     * @param array $options
     * @return array
     */
    public function getInstantiated(array $options = [])
    {
        $benchmarks = [];

        foreach (self::BENCHMARKS as $name => $class) {
            try {
                $instance = new $class($options);
            } catch (\Exception $e) {
                $instance = [
                    'fail' => 'instantiation',
                    'message' => $e->getMessage(),
                ];
            }
            $benchmarks[$name] = $instance;
        }

        return $benchmarks;
    }

    /**
     * @return array
     */
    public function getBenchmarks()
    {
        return self::BENCHMARKS;
    }

    /**
     * @return array
     */
    public function listBenchmarks()
    {
        return array_keys(self::BENCHMARKS);
    }
}
