<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Strings;
use BenchmarkPHP\Tests\TestHelpersTrait;

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
    public function testConstructorThrowExceptionWhenEmptyFunctions()
    {
        $this->expectException(\LogicException::class);

        $method = $this->getPrivateMethod($this->bench, 'initFunctions');

        $method->invokeArgs($this->bench, [[]]);
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
        $method = $this->getPrivateMethod($this->bench, 'generateTestData');
        $data = $method->invoke($this->bench);

        $this->assertCount($this->bench->getIterations(), $data);
        $this->assertInternalType('string', $data[mt_rand(1, $this->bench->getIterations())]);
    }
}
