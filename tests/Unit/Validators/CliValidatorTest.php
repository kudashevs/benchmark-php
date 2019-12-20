<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Validators\CliValidator;
use BenchmarkPHP\Validators\ValidatorInterface;

class CliValidatorTest extends TestCase
{
    /** @var CliValidator */
    private $validator;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->validator = new CliValidator();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(ValidatorInterface::class, $this->validator);
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
        $result = $this->validator->validate($_SERVER['argv']);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('-a', $result);
    }
}
