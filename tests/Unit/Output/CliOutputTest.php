<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Output;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Output\CliOutput;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Exceptions\RuntimeException;

class CliOutputTest extends TestCase
{
    /**
     * @var CliOutput
     */
    private $output;

    protected function setUp()
    {
        $this->output = new CliOutput();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(OutputInterface::class, $this->output);
    }

    /**
     * Exceptions.
     */
    public function testWriteThrowExceptionWhenDataIsNotString()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to write a non string data');

        $this->output->write(['test']);
    }

    /**
     * Corner cases.
     */
    public function testTerminateOnErrorReturnsExpectedWhenCodeIsLessThan1()
    {
        $errorCode = -1;
        $defaultCode = 1;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $output->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs0()
    {
        $errorCode = 0;
        $defaultCode = 1;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $output->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs1()
    {
        $errorCode = 1;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $output->terminateOnError();
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIs254()
    {
        $errorCode = 254;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $output->terminateOnError($errorCode);
    }

    public function testTerminateOnErrorReturnsExpectedWhenCodeIsMoreThan254()
    {
        $errorCode = 255;
        $defaultCode = 1;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($defaultCode);

        $output->terminateOnError($errorCode);
    }

    /**
     * Functionality.
     */
    public function testWriteBehavesExpected()
    {
        $message = 'message';

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['writeRaw']);
        $output->expects($this->once())
            ->method('writeRaw')
            ->with(
                $this->isType('resource'),
                $message
            );

        $output->write($message);
    }

    public function testErrorBehavesExpected()
    {
        $error = 'error message';

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['writeRaw']);
        $output->expects($this->once())
            ->method('writeRaw')
            ->with(
                $this->isType('resource'),
                $error
            );

        $output->error($error);
    }

    public function testTerminateOnSuccessReturnsZeroCode()
    {
        $successCode = 0;

        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($successCode);

        $output->terminateOnSuccess();
    }

    public function testTerminateOnErrorReturnsExpected()
    {
        $errorCode = 42;
        $output = $this->getMockWithMethods(['terminate']);
        $output->expects($this->once())
            ->method('terminate')
            ->with($errorCode);

        $output->terminateOnError($errorCode);
    }

    /**
     * Helpers.
     */

    /**
     * @param array $methods
     * @return CliOutput|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockWithMethods(array $methods)
    {
        /** @var CliOutput|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockBuilder(CliOutput::class)
            ->setMethods($methods)
            ->getMock();

        return $output;
    }
}
