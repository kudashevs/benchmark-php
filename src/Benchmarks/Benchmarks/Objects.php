<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks\Benchmarks;

class Objects extends AbstractFunctionsBasedBenchmark
{
    /**
     * @var array
     */
    const FUNCTIONS = [
        'get_class_methods',
        'get_class',
        'get_object_vars',
        'get_object_vars',
        'is_object',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\getPublic',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\setPublic',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\getProtected',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\setProtected',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\getPrivate',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\setPrivate',
        'BenchmarkPHP\Benchmarks\Benchmarks\Objects\castToArray',
    ];

    /**
     * @return array
     */
    protected function generateTestData()
    {
        $obj = new Dummy();
        $data = [];

        for ($i = 1; $i <= $this->iterations; $i++) {
            $data[$i] = $obj;
        }

        return $data;
    }
}

class Dummy
{
    /**
     * @var mixed
     */
    public $public = 'public data';

    /**
     * @var mixed
     */
    protected $protected = 'protected data';

    /**
     * @var mixed
     */
    private $private = 'private data';

    /**
     * @return mixed
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param mixed $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * @return mixed
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * @param mixed $protected
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;
    }
}

namespace BenchmarkPHP\Benchmarks\Benchmarks\Objects;

use BenchmarkPHP\Benchmarks\Benchmarks\Dummy;

function getPublic(Dummy $object)
{
    return $object->public;
}

function setPublic(Dummy $object)
{
    $object->public = 'updated data';
}

function getProtected(Dummy $object)
{
    return $object->getProtected();
}

function setProtected(Dummy $object)
{
    $object->setProtected('updated data');
}

function getPrivate(Dummy $object)
{
    return $object->getPrivate();
}

function setPrivate(Dummy $object)
{
    $object->setPrivate('updated data');
}

function castToArray(Dummy $object)
{
    return (array)$object;
}
