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
use BenchmarkPHP\Output\CliOutput;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Presenters\CliPresenter;
use BenchmarkPHP\Exceptions\RuntimeException;
use BenchmarkPHP\Presenters\PresenterFactory;

class PresenterFactoryTest extends TestCase
{
    /**
     * @var PresenterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new PresenterFactory();
    }

    /**
     * Exceptions.
     */
    public function testCreateThrowExceptionWhenUnknownClassName()
    {
        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(OutputInterface::class)
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
        $inputObject = new CliOutput();

        $this->assertInstanceOf(CliPresenter::class, $this->factory->create($inputObject));
    }
}
