<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Entries;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Entries\Command;

class CommandTest extends TestCase
{
    /** @var Command */
    private $command;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->command = new Command();
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
    public function testInvokeReturnsExpected()
    {
        $parsed = call_user_func($this->command);

        $this->assertInternalType('array', $parsed);
        $this->assertArrayHasKey('-a', $parsed);
    }
}
