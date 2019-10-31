<?php

namespace BenchmarkPHP\Tests\Reporters;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Reporters\CliReporter;
use BenchmarkPHP\Tests\TestHelpersTrait;

class CliReporterTest extends TestCase
{
    use TestHelpersTrait;

    /** @var CliReporter */
    private $reporter;

    protected function setUp()
    {
        $this->reporter = new CliReporter();
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
        $this->assertContains(CliReporter::REPORT_ROW, $this->getActualOutput());
        $this->assertContains(CliReporter::REPORT_COLUMN, $this->getActualOutput());
        $this->assertContains(CliReporter::REPORT_SPACE, $this->getActualOutput());
    }

    public function testShowFooterReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        $this->reporter->showFooter($data);

        $this->expectOutputRegex('/' . $expected . '/');
        $this->assertContains(CliReporter::REPORT_ROW, $this->getActualOutput());
        $this->assertNotContains(CliReporter::REPORT_COLUMN, $this->getActualOutput());
    }

    public function testShowBlockReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345' . PHP_EOL;

        $this->reporter->showBlock($data);

        $this->expectOutputString($expected);
        $this->assertNotContains(CliReporter::REPORT_ROW, $this->getActualOutput());
        $this->assertNotContains(CliReporter::REPORT_COLUMN, $this->getActualOutput());
    }

    public function testShowSeparatorReturnsExpected()
    {
        $expected = CliReporter::REPORT_WIDTH;

        $this->reporter->showSeparator();

        $this->expectOutputRegex('/' . CliReporter::REPORT_ROW . '/');
        $this->assertEquals($expected, mb_strlen(trim($this->getActualOutput())));
    }

    public function testFormatInputReturnsEmptyWhenWrongType()
    {
        $method = $this->getPrivateMethod($this->reporter, 'formatInput');

        $this->assertEquals('' . PHP_EOL, $method->invokeArgs($this->reporter, [1]));
    }

    public function testFormatInputReturnsExpectedWhenListStyledInput()
    {
        $input = ['first', 'second'];

        $method = $this->getPrivateMethod($this->reporter, 'formatInput');
        $result = $method->invokeArgs($this->reporter, [$input, 'list']);

        $this->assertStringStartsWith(CliReporter::LIST_BULLET, $result);
    }

    public function testFormatInputReturnsExpectedWhenListStyledInputContainsExclusion()
    {
        $input = ['exclude:header', 'first', 'second'];
        $expected = 'header';

        $method = $this->getPrivateMethod($this->reporter, 'formatInput');
        $result = $method->invokeArgs($this->reporter, [$input, 'list']);

        $this->assertStringStartsWith($expected, $result);
    }

    public function testMakeCenteredReturnsEmptyWhenWrongType()
    {
        $method = $this->getPrivateMethod($this->reporter, 'makeCentered');

        $this->assertEquals(CliReporter::REPORT_WIDTH - 2, mb_strlen($method->invokeArgs($this->reporter, [1])));
    }

    public function testMakeCenteredReturnsClippedWhenStringLargerThanWidth()
    {
        $string = str_repeat('string', CliReporter::REPORT_WIDTH);
        $expected = substr($string, 0, CliReporter::REPORT_WIDTH - 2);

        $method = $this->getPrivateMethod($this->reporter, 'makeCentered');

        $this->assertEquals($expected, $method->invokeArgs($this->reporter, [$string]));
    }

    public function testMakeCenteredReturnsExpectedWhenStringIsOdd()
    {
        $width = CliReporter::REPORT_WIDTH - 2;
        if ($width % 2 === 0) {
            $string = 'odd';
        } else {
            $string = 'even';
        }

        $length = mb_strlen($string);
        $half = ($width - $length) / 2;

        $expected = str_repeat(CliReporter::REPORT_SPACE, floor($half)) . $string . str_repeat(CliReporter::REPORT_SPACE, ceil($half));

        $method = $this->getPrivateMethod($this->reporter, 'makeCentered');
        $this->assertEquals($expected, $method->invokeArgs($this->reporter, [$string]));
    }
}
