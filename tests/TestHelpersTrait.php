<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests;

trait TestHelpersTrait
{
    /**
     * @param object $obj
     * @param string $methodName
     * @param array $args
     * @throws \ReflectionException
     * @return mixed
     */
    public function runPrivateMethod($obj, $methodName, array $args = [])
    {
        $method = $this->getPrivateMethod($obj, $methodName);

        if (empty($args)) {
            return $method->invoke($obj);
        }

        return $method->invokeArgs($obj, $args);
    }

    /**
     * @param object $obj
     * @param string $methodName
     * @throws \ReflectionException
     * @return \ReflectionMethod
     */
    public function getPrivateMethod($obj, $methodName)
    {
        $reflection = new \ReflectionClass($obj);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param object $obj
     * @param string $valueName
     * @param mixed $newValue
     * @throws \ReflectionException
     * @return void
     */
    public function setPrivateVariableValue($obj, $valueName, $newValue)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);
        $property->setValue($obj, $newValue);
    }

    /**
     * @param object $obj
     * @param string $valueName
     * @throws \ReflectionException
     * @return mixed
     */
    public function getPrivateVariableValue($obj, $valueName)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
