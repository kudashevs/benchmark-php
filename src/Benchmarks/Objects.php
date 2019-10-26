<?php

namespace BenchmarkPHP\Benchmarks;

class Objects extends AbstractBenchmark
{
    private $functions = [
        'get_class_methods',
        'get_class',
        'get_object_vars',
        'get_object_vars',
        'is_object',
        'BenchmarkPHP\Benchmarks\getPublic',
        'BenchmarkPHP\Benchmarks\setPublic',
        'BenchmarkPHP\Benchmarks\getProtected',
        'BenchmarkPHP\Benchmarks\setProtected',
        'BenchmarkPHP\Benchmarks\getPrivate',
        'BenchmarkPHP\Benchmarks\setPrivate',
    ];

    /**
     * Create a new Objects instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->functions = $this->initFunctions($this->functions);
    }

    /**
     * @param array $functions
     * @throws \LogicException
     * @return array
     */
    protected function initFunctions(array $functions)
    {
        foreach ($functions as $key => $function) {
            if (!function_exists($function)) {
                unset($functions[$key]);
            }
        }

        if (empty($functions)) {
            throw new \LogicException('There is no functions to proceed.');
        }

        return $functions;
    }

    /**
     * @return void
     */
    public function before()
    {
        $this->data = $this->generateData();
    }

    /**
     * @return void
     */
    public function handle()
    {
        foreach ($this->functions as $function) {
            foreach ($this->data as $i) {
                $function($i);
            }
        }
    }

    /**
     * @return void
     */
    public function after()
    {
        $this->data = null;
    }

    /**
     * @return array
     */
    protected function generateData()
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