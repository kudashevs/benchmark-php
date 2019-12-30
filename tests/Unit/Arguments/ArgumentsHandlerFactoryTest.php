<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Arguments;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Input\CliInput;
use BenchmarkPHP\Input\InputInterface;
use BenchmarkPHP\Exceptions\RuntimeException;
use BenchmarkPHP\Arguments\CliArgumentsHandler;
use BenchmarkPHP\Arguments\ArgumentsHandlerFactory;

class ArgumentsHandlerFactoryTest extends TestCase
{
    /**
     * @var ArgumentsHandlerFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ArgumentsHandlerFactory();
    }

    /**
     * Exceptions.
     */
    public function testCreateThrowExceptionWhenUnknownClassName()
    {
        /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(InputInterface::class)
            ->getMock();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create correct');

        $this->factory->create($mock);
    }

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testCreateCreatesCorrectInstanceWhenKnownObject()
    {
        $inputObject = new CliInput();

        $this->assertInstanceOf(CliArgumentsHandler::class, $this->factory->create($inputObject));
    }
}
