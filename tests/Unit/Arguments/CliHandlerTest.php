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

use BenchmarkPHP\Application;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Arguments\CliHandler;
use BenchmarkPHP\Arguments\ArgumentsHandlerInterface;
use BenchmarkPHP\Exceptions\ValidationException;

class CliValidatorTest extends TestCase
{
    /** @var CliHandler */
    private $validator;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->validator = new CliHandler([]);
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(ArgumentsHandlerInterface::class, $this->validator);
    }

    /**
     * Exceptions.
     */
    public function testValidateThrowExceptionWhenRequiredArgumentDoesntHaveValue()
    {
        $require = current(Application::REQUIRE_VALUE_ARGUMENTS);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Empty value');

        $this->validator->validate([$require]);
    }

    public function testValidateThrowExceptionWhenRequiredArgumentIsLikeAnOption()
    {
        $require = current(Application::REQUIRE_VALUE_ARGUMENTS);
        $value = '-c';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Wrong value ' . $value);

        $this->validator->validate([$require, $value]);
    }

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
