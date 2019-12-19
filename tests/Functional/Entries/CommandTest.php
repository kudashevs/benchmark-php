<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Functional\Entries;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Entries\Command;
use BenchmarkPHP\Tests\TestHelpersTrait;

class CommandTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Command */
    private $command;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->command = new Command();
    }

    /**
     * Functionality.
     */

    /**
     * @dataProvider provideInitArgumentsData
     * @param array $arguments
     * @param array $required
     * @param string $verify
     * @param string $message
     * @throws \ReflectionException
     */
    public function testInitArgumentsExecutesTerminateMethod($arguments, $required, $verify, $message)
    {
        $partialMock = $this->getMockBuilder(Command::class)
            ->setMethods(['terminateWithMessage'])
            ->getMock();
        $partialMock->expects($this->once())
            ->method('terminateWithMessage')
            ->with($this->stringContains($verify))
            ->will($this->throwException(new \InvalidArgumentException($message)));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        $this->runPrivateMethod($partialMock, 'initArguments', [$arguments, $required]);
    }

    public function provideInitArgumentsData()
    {
        return [
            'When required value for option -b is missed' => [
                array_merge($_SERVER['argv'], ['-b']),
                ['-b'],
                'empty',
                'Passed value is empty.',
            ],
            'When required value for option -b looks like another option' => [
                array_merge($_SERVER['argv'], ['-b', '-c']),
                ['-b'],
                'wrong',
                'Passed value looks like option.',
            ],
            'When one of required values looks like another option' => [
                array_merge($_SERVER['argv'], ['-c', 'path', '--benchmarks', '--debug']),
                ['-c', '--benchmarks'],
                'wrong',
                'One of passed values looks like option.',
            ],

        ];
    }
}
