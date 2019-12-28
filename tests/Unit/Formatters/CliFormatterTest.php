<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Reporters;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Reporters\CliFormatter;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Reporters\FormatterInterface;

class CliFormatterTest extends TestCase
{
    use TestHelpersTrait;

    /** @var CliFormatter */
    private $reporter;

    protected function setUp()
    {
        $this->reporter = new CliFormatter();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(FormatterInterface::class, $this->reporter);
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
    public function testShowBlockReturnsExpectedWhenString()
    {
        $input = 'version';
        $expected = $input . PHP_EOL;

        $this->reporter->showBlock($input);

        $this->expectOutputString($expected);
    }

    public function testShowBlockReturnsExpectedWhenIndexedArray()
    {
        $input = ['first', 'second'];
        $expected = 'first' . PHP_EOL . 'second' . PHP_EOL;

        $this->reporter->showBlock($input);

        $this->expectOutputString($expected);
    }

    public function testShowBlockReturnsExpectedWhenAssociativeArray()
    {
        $input = [
            'first' => 0.12345,
            'second' => 'test',
        ];
        $expected = 'first: 0.12345' . PHP_EOL . 'second: test' . PHP_EOL;

        $this->reporter->showBlock($input);

        $this->expectOutputString($expected);
    }

    public function testShowHeaderReturnsExpected()
    {
        $data = [
            'version' => '1.0.0',
        ];

        $this->reporter->showHeader($data);

        $this->expectOutputRegex('/' . $data['version'] . '/');
        $this->assertContains(CliFormatter::REPORT_ROW, $this->getActualOutput());
        $this->assertContains(CliFormatter::REPORT_COLUMN, $this->getActualOutput());
        $this->assertContains(CliFormatter::REPORT_SPACE, $this->getActualOutput());
    }

    public function testShowFooterReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        $this->reporter->showFooter($data);

        $this->expectOutputRegex('/' . $expected . '/');
        $this->assertContains(CliFormatter::REPORT_ROW, $this->getActualOutput());
        $this->assertNotContains(CliFormatter::REPORT_COLUMN, $this->getActualOutput());
    }

    public function testShowBlockReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345' . PHP_EOL;

        $this->reporter->showBlock($data);

        $this->expectOutputString($expected);
        $this->assertNotContains(CliFormatter::REPORT_ROW, $this->getActualOutput());
        $this->assertNotContains(CliFormatter::REPORT_COLUMN, $this->getActualOutput());
    }

    public function testShowSeparatorReturnsExpected()
    {
        $expected = CliFormatter::REPORT_WIDTH;

        $this->reporter->showSeparator();

        $this->expectOutputRegex('/' . CliFormatter::REPORT_ROW . '/');
        $this->assertEquals($expected, mb_strlen(trim($this->getActualOutput())));
    }
}
