<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Traits\PluralizeTrait;

class PluralizeTraitTest extends TestCase
{
    use PluralizeTrait;

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testGeneratePluralizedReturnsExpectedWhenZeroResult()
    {
        $result = $this->generatePluralized(0, 'benchmark');

        $this->assertEquals('0 benchmarks', $result);
    }

    public function testGeneratePluralizedReturnsExpectedWhenOneResult()
    {
        $result = $this->generatePluralized(1, 'benchmark');

        $this->assertEquals('1 benchmark', $result);
    }

    public function testGeneratePluralizedReturnsExpectedWhenThreeResult()
    {
        $result = $this->generatePluralized(3, 'benchmark');

        $this->assertEquals('3 benchmarks', $result);
    }

    /**
     * Captured bugs.
     */
    public function testGeneratePluralizedCountReturnsExpectedWhenFourResultAndTextWithExtraS()
    {
        $result = $this->generatePluralized(4, 'tests');

        $this->assertEquals('4 tests', $result);
    }
}
