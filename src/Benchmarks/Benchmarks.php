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
     * @return array
     */
    public function get()
    {
        return self::BENCHMARKS;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return array_keys(self::BENCHMARKS);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getInstances(array $options = [])
    {
        $requested = !empty($options['benchmarks']) ? array_intersect_key(self::BENCHMARKS, $options['benchmarks']) : self::BENCHMARKS;
        $benchmarks = [];

        foreach ($requested as $name => $class) {
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
}
