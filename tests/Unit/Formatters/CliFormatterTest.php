<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Formatters;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Formatters\CliFormatter;
use BenchmarkPHP\Formatters\FormatterInterface;

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

        $result = $this->reporter->block($input);

        $this->assertContains($expected, $result);
    }

    public function testShowBlockReturnsExpectedWhenIndexedArray()
    {
        $input = ['first', 'second'];
        $expected = 'first' . PHP_EOL . 'second' . PHP_EOL;

        $result = $this->reporter->block($input);

        $this->assertContains($expected, $result);
    }

    public function testShowBlockReturnsExpectedWhenAssociativeArray()
    {
        $input = [
            'first' => 0.12345,
            'second' => 'test',
        ];
        $expected = 'first: 0.12345' . PHP_EOL . 'second: test' . PHP_EOL;

        $result = $this->reporter->block($input);

        $this->assertContains($expected, $result);
    }

    public function testShowHeaderReturnsExpected()
    {
        $data = [
            'version' => '1.0.0',
        ];

        $result = $this->reporter->header($data);

        $this->assertRegExp('/' . $data['version'] . '/', $result);
        $this->assertContains(CliFormatter::REPORT_ROW, $result);
        $this->assertContains(CliFormatter::REPORT_COLUMN, $result);
        $this->assertContains(CliFormatter::REPORT_SPACE, $result);
    }

    public function testShowFooterReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        $result = $this->reporter->footer($data);

        $this->assertRegExp('/' . $expected . '/', $result);
        $this->assertContains(CliFormatter::REPORT_ROW, $result);
        $this->assertNotContains(CliFormatter::REPORT_COLUMN, $result);
    }

    public function testShowBlockReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345' . PHP_EOL;

        $result = $this->reporter->block($data);

        $this->assertContains($expected, $result);
        $this->assertNotContains(CliFormatter::REPORT_ROW, $result);
        $this->assertNotContains(CliFormatter::REPORT_COLUMN, $result);
    }

    public function testShowSeparatorReturnsExpected()
    {
        $expected = CliFormatter::REPORT_WIDTH;

        $result = $this->reporter->separator();

        $this->assertRegExp('/' . CliFormatter::REPORT_ROW . '/', $result);
        $this->assertEquals($expected, mb_strlen(trim($result)));
    }
}
