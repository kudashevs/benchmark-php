<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Integers;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\HandlesFunctionsTrait;

class HandlesFunctionsTraitTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Integers $bench Must use HandlesFunctionsTrait */
    protected $bench;

    protected function setUp()
    {
        $this->bench = new Integers();

        if (!array_key_exists(HandlesFunctionsTrait::class, class_uses($this->bench))) {
            throw new \LogicException(get_class($this->bench) . ' doesn\'t use HandlesFunctionsTrait. Check setUp() method.');
        }
    }

    // Exceptions.

    // Corner cases.

    // Functionality.
    public function testGetFunctionsSummaryReturnExpected()
    {
        $method = $this->getPrivateMethod($this->bench, 'getFunctionsSummary');

        $result = $method->invoke($this->bench);
        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('executed', $result);
    }
}
