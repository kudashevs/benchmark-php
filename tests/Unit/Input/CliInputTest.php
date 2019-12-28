<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Input;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Input\CliInput;
use BenchmarkPHP\Input\InputInterface;

class CliInputTest extends TestCase
{
    /**
     * @var CliInput
     */
    private $input;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->input = new CliInput();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(InputInterface::class, $this->input);
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
    public function testArgumentsReturnsExpectedType()
    {
        $arguments = $this->input->arguments();

        $this->assertInternalType('array', $arguments);
    }

    public function testArgumentsReturnsWithRemovedFirstElement()
    {
        $arguments = $this->input->arguments();

        $this->assertInternalType('array', $arguments);
        $this->assertCount(1, $arguments);
    }
}
