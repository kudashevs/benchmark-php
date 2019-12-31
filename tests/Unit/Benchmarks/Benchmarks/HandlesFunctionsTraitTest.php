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
use BenchmarkPHP\Benchmarks\Benchmarks\Integers;
use BenchmarkPHP\Benchmarks\Benchmarks\HandlesFunctionsTrait;

class HandlesFunctionsTraitTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Integers $bench The class must use HandlesFunctionsTrait */
    protected $bench;

    protected function setUp()
    {
        $this->bench = new Integers();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceUsesCertainTrait()
    {
        $this->assertContains(HandlesFunctionsTrait::class, class_uses($this->bench));
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
    public function testGetFunctionsSummaryReturnExpected()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);
        $result = $this->runPrivateMethod($this->bench, 'getFunctionsSummary');

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('execute', $result);
    }

    public function testGetFunctionsList()
    {
        $result = $this->runPrivateMethod($this->bench, 'getFunctionsList');

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('executed functions', $result);
    }
}
