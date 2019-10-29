<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\HandlesFunctionsTrait;

class HandlesFunctionsTraitTest extends TestCase
{
    use TestHelpersTrait;

    /** @var HandlesFunctionsTrait */
    protected $bench;

    protected function setUp()
    {
        $this->bench = $this->getMockForTrait(HandlesFunctionsTrait::class);
    }

    // Exceptions.

    // Corner cases.

    // Functionality.
    public function testGetFunctionsSummaryReturnExpected()
    {
        $method = $this->getPrivateMethod($this->bench, 'getFunctionsSummary');

        $result = $method->invoke($this->bench);
        $this->assertArrayHasKey('executed', $result);
        $this->assertArrayHasKey('skipped', $result);
    }
}
