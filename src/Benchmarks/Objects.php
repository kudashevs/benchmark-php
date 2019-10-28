<?php

namespace BenchmarkPHP\Benchmarks;

class Objects extends AbstractBenchmark
{
    use HandlesFunctionsTrait;

    /**
     * @var array
     */
    const INIT_FUNCTIONS = [
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
        'BenchmarkPHP\Benchmarks\convertToArray',
    ];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * Create a new Objects instance.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->functions = $this->initFunctions(self::INIT_FUNCTIONS);
    }

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

function convertToArray(Dummy $object)
{
    return (array)$object;
}
