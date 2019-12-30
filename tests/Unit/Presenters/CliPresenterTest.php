<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Presenters;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Presenters\CliPresenter;
use BenchmarkPHP\Presenters\PresenterInterface;

class CliPresenterTest extends TestCase
{
    use TestHelpersTrait;

    /** @var CliPresenter */
    private $presenter;

    protected function setUp()
    {
        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
        $this->presenter = new CliPresenter($stub);
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(PresenterInterface::class, $this->presenter);
    }

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */

    /**
     * @param string $method
     * @param mixed $input
     * @param string $expected
     * @dataProvider provideOutputInterfaceMethods
     */
    public function testOutputInterfaceMethodsBehavesExpected($method, $input, $expected)
    {
        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('write')
            ->with($this->stringContains($expected));

        $presenter = new CliPresenter($mock);

        $presenter->$method($input);
    }

    /**
     * @return array
     */
    public function provideOutputInterfaceMethods()
    {
        return [
            'version with string' => ['version', 'version 1.0.0', 'version 1.0.0'],
            'header with string' => ['header', 'test head', 'test head'],
            'header with indexed array' => ['header', ['1.1.0'], '1.1.0'],
            'header with assoc array' => ['header', ['version' => '1.2.0'], '1.2.0'],
            'footer with string' => ['footer', 'test foot', 'test foot'],
            'footer with indexed array' => ['footer', ['1.1.0'], '1.1.0'],
            'footer with assoc array' => ['footer', ['version' => '1.2.0'], '1.2.0'],
            'block with string' => ['block', 'test block', 'test block'],
            'block with indexed array' => ['block', ['1.1.0'], '1.1.0'],
            'block with assoc array' => ['block', ['version' => '1.2.0'], '1.2.0'],
            'listing with string' => ['listing', 'test list', 'test list'],
            'listing with indexed array' => ['listing', ['1.1.0'], '1.1.0'],
            'listing with assoc array' => ['listing', ['version' => '1.2.0'], '1.2.0'],
            'separator contains' => ['separator', null, CliPresenter::REPORT_ROW],
        ];
    }
}
