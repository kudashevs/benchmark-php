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
use BenchmarkPHP\Traits\VerbosityTrait;

class VerbosityTraitTest extends TestCase
{
    use VerbosityTrait;

    private $options = [];

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testIsSilentModeReturnsExpectedWhenTrue()
    {
        $this->setOptions(['debug' => false, 'verbose' => false]);

        $result = $this->isSilentMode();

        $this->assertTrue($result);
    }

    public function testIsSilentModeReturnsExpectedWhenFalse()
    {
        $this->setOptions(['debug' => true, 'verbose' => false]);

        $result = $this->isSilentMode();

        $this->assertFalse($result);
    }

    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setOptions(['debug' => true]);

        $result = $this->isDebugMode();

        $this->assertTrue($result);
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setOptions(['debug' => false]);

        $result = $this->isDebugMode();

        $this->assertFalse($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setOptions(['verbose' => true]);

        $result = $this->isVerboseMode();

        $this->assertTrue($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setOptions(['verbose' => false]);

        $result = $this->isVerboseMode();

        $this->assertFalse($result);
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
