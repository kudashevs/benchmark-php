<?php

namespace BenchmarkPHP\Tests;

trait TestHelpers
{
    /**
     * @param object $obj
     * @param string $methodName
     * @return \ReflectionMethod
     * @throws \ReflectionException
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
     * @return void
     * @throws \ReflectionException
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
     * @return mixed
     * @throws \ReflectionException
     */
    public function getPrivateVariableValue($obj, $valueName)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}