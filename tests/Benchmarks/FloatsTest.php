<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Floats;
use BenchmarkPHP\Tests\TestHelpersTrait;

class FloatsTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Floats */
    private $bench;

    protected function setUp()
    {
        $this->bench = new Floats(['testing' => true]);
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
    public function testGenerateTestDataDoesNotGenerateZero()
    {
        $data = $this->runPrivateMethod($this->bench, 'generateTestData');

        $this->assertCount($this->bench->getIterations(), $data);
        $this->assertNotContains(0, $data);
    }

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
        $this->assertInternalType('float', $data[mt_rand(1, $this->bench->getIterations())]);
    }
}
