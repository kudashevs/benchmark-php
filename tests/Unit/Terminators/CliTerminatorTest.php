<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Terminators;

use BenchmarkPHP\Terminators\CliTerminator;
use BenchmarkPHP\Terminators\TerminatorInterface;
use PHPUnit\Framework\TestCase;

class CliTerminatorTest extends TestCase
{
    /**
     * @var CliTerminator
     */
    private $terminator;

    protected function setUp()
    {
        $this->terminator = new CliTerminator();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(TerminatorInterface::class, $this->terminator);
    }

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */
    public function testTerminateOnErrorReturnsExpectedWhenCodeIsLessThan1()
    {
        $errorCode = -1;
        $defaultCode = 1;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $terminator->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs0()
    {
        $errorCode = 0;
        $defaultCode = 1;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $terminator->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs1()
    {
        $errorCode = 1;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $terminator->terminateOnError();
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs254()
    {
        $errorCode = 254;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $terminator->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIsMoreThan254()
    {
        $errorCode = 255;
        $defaultCode = 1;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $terminator->terminateOnError($errorCode);
    }



    /**
     * Functionality.
     */
    public function testTerminateOnSuccessReturnsZeroCode()
    {
        $successCode = 0;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($successCode);

        $terminator->terminateOnSuccess();
    }

    public function testTerminateOnErrorReturnsExpected()
    {
        $errorCode = 42;

        /** @var CliTerminator|\PHPUnit_Framework_MockObject_MockObject $terminator */
        $terminator = $this->getMockBuilder(CliTerminator::class)
            ->setMethods(['terminate'])
            ->getMock();

        $terminator->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $terminator->terminateOnError($errorCode);
    }
}
