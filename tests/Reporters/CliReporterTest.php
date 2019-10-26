<?php

namespace BenchmarkPHP\Tests\Reporters;

use BenchmarkPHP\Tests\TestHelpers;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Reporters\CliReporter;

class CliReporterTest extends TestCase
{
    use TestHelpers;

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
    public function testShowHeaderReturnsExpected()
    {
        $data = [
            'version' => '1.0.0',
        ];

        $result = $this->reporter->showHeader($data);
        $this->assertContains(CliReporter::REPORT_ROW, $result);
        $this->assertContains(CliReporter::REPORT_COLUMN, $result);
        $this->assertContains($data['version'], $result);
    }

    public function testShowFooterReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        $result = $this->reporter->showFooter($data);
        $this->assertContains($expected, $result);
        $this->assertContains(CliReporter::REPORT_ROW, $result);
        $this->assertNotContains(CliReporter::REPORT_COLUMN, $result);
    }

    public function testShowBlockReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        $result = $this->reporter->showBlock($data);
        $this->assertContains($expected, $result);
        $this->assertNotContains(CliReporter::REPORT_ROW, $result);
        $this->assertNotContains(CliReporter::REPORT_COLUMN, $result);
    }

    public function testShowSeparatorReturnsExpected()
    {
        $expected = CliReporter::REPORT_WIDTH;

        $result = $this->reporter->showSeparator();
        $this->assertEquals($expected, mb_strlen(trim($result)));
        $this->assertContains(CliReporter::REPORT_ROW, $result);
        $this->assertNotContains(CliReporter::REPORT_COLUMN, $result);
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
