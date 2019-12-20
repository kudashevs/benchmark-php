<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Benchmarks\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\Benchmarks\Strings;

class StringsTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Strings */
    private $bench;

    protected function setUp()
    {
        $this->bench = new Strings(['testing' => true]);
    }

    /**
     * Exceptions.
     */
    public function testConstructorThrowsExceptionWhenEmptyFunctions()
    {
        $this->expectException(\LogicException::class);
        $this->runPrivateMethod($this->bench, 'initFunctions', [[]]);
    }

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testBeforeCreatesData()
    {
        $this->bench->before();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertCount($this->bench->getIterations(), $data);
    }

    public function testAfterRemovesData()
    {
        $this->bench->before();
        $this->bench->after();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertEmpty($data);
    }

    public function testResultReturnsExpected()
    {
        $this->bench->before();
        $this->bench->handle();
        $this->bench->after();

        $result = $this->bench->result();
        $this->assertNotContains('Not handled yet', $result);
        $this->assertContainsOnly('float', $result);
    }

    public function testGenerateTestDataReturnsExpected()
    {
        $data = $this->runPrivateMethod($this->bench, 'generateTestData');

        $this->assertCount($this->bench->getIterations(), $data);
        $this->assertInternalType('string', $data[mt_rand(1, $this->bench->getIterations())]);
    }
}
