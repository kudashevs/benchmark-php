<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Integration\Presenters;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Presenters\CliPresenter;
use BenchmarkPHP\Presenters\PresenterInterface;
use BenchmarkPHP\Tests\Integration\Dummies\DummyOutput;

class CliPresenterTest extends TestCase
{
    /** @var CliPresenter */
    private $presenter;

    protected function setUp()
    {
        /** @var OutputInterface $dummy */
        $dummy = new DummyOutput();
        $this->presenter = new CliPresenter($dummy);
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(PresenterInterface::class, $this->presenter);
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
    public function testVersionReturnsExpectedWhenString()
    {
        $input = 'version 1.0.0';
        $expected = $input . CliPresenter::NEW_LINE . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->version($input);
    }

    public function testHeaderReturnsExpected()
    {
        $data = [
            'version' => '1.0.0',
        ];

        ob_start();
        $this->presenter->header($data);
        $result = ob_get_clean();

        $this->assertRegExp('/' . $data['version'] . '/', $result);
        $this->assertContains(CliPresenter::REPORT_ROW, $result);
        $this->assertContains(CliPresenter::REPORT_COLUMN, $result);
        $this->assertContains(CliPresenter::REPORT_SPACE, $result);
    }

    public function testFooterReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345';

        ob_start();
        $this->presenter->footer($data);
        $result = ob_get_clean();

        $this->assertRegExp('/' . $expected . '/', $result);
        $this->assertContains(CliPresenter::REPORT_ROW, $result);
        $this->assertNotContains(CliPresenter::REPORT_COLUMN, $result);
    }

    public function testBlockReturnsExpectedWhenString()
    {
        $input = 'version';
        $expected = $input . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->block($input);
    }

    public function testBlockReturnsExpectedWhenIndexedArray()
    {
        $input = ['first', 'second'];
        $expected = 'first' . CliPresenter::NEW_LINE . 'second' . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->block($input);
    }

    public function testBlockReturnsExpectedWhenAssociativeArray()
    {
        $input = [
            'first' => 0.12345,
            'second' => 'test',
        ];
        $expected = 'first: 0.12345' . CliPresenter::NEW_LINE . 'second: test' . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->block($input);
    }

    public function testListingReturnsExpectedWhenString()
    {
        $input = 'listing';
        $expected = CliPresenter::LIST_BULLET . $input . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->listing($input);
    }

    public function testListingReturnsExpectedWhenIndexedArray()
    {
        $input = ['first', 'second'];
        $expected = CliPresenter::LIST_BULLET . 'first' . CliPresenter::NEW_LINE . CliPresenter::LIST_BULLET . 'second' . CliPresenter::NEW_LINE;

        $this->expectOutputString($expected);
        $this->presenter->listing($input);
    }

    public function testBlockReturnsExpected()
    {
        $data = [
            'stat' => 0.12345,
        ];
        $expected = 'stat: 0.12345' . CliPresenter::NEW_LINE;

        ob_start();
        $this->presenter->block($data);
        $result = ob_get_clean();

        $this->assertContains($expected, $result);
        $this->assertNotContains(CliPresenter::REPORT_ROW, $result);
        $this->assertNotContains(CliPresenter::REPORT_COLUMN, $result);
    }

    public function testSeparatorReturnsExpected()
    {
        $expected = CliPresenter::REPORT_WIDTH;

        ob_start();
        $this->presenter->separator();
        $result = ob_get_clean();

        $this->assertRegExp('/' . CliPresenter::REPORT_ROW . '/', $result);
        $this->assertEquals($expected, mb_strlen(trim($result)));
    }
}
