<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit;

use BenchmarkPHP\Application;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Input\InputInterface;
use BenchmarkPHP\Output\OutputInterface;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\Benchmarks\Integers;

class ApplicationTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Application */
    private $app;

    protected function setUp()
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = ['-a'];

        /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject $handler */
        $input = $this->getMockBuilder(InputInterface::class)
            ->setMockClassName('CliInput')
            ->getMock();
        $input
            ->method('arguments')
            ->willReturn($_SERVER['argv']);
        /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject $output */
        $output = $this->getMockBuilder(OutputInterface::class)
            ->setMockClassName('CliOutput')
            ->getMock();

        $this->app = new Application($input, $output);
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
    public function testHandleBenchmarksExecutesBeforeHandle()
    {
        $app = $this->getInstanceWithSkippedConstructor();

        $this->runPrivateMethod($app, 'handleBenchmarks', [[]]);

        $this->assertContains(date(Application::DATE_FORMAT), $app->getStatistics(['started_at']));
    }

    public function testHandleBenchmarksExecutesAfterHandle()
    {
        $app = $this->getInstanceWithSkippedConstructor();

        $this->runPrivateMethod($app, 'handleBenchmarks', [[]]);

        $this->assertContains(date(Application::DATE_FORMAT), $app->getStatistics(['stopped_at']));
    }

    public function testHandleBenchmarksExecutesContractMethodsOnBenchmark()
    {
        $mock = $this->getMockBuilder(Integers::class)
            ->getMock();
        $mock->expects($this->once())
            ->method('before');
        $mock->expects($this->once())
            ->method('handle');
        $mock->expects($this->once())
            ->method('after');
        $mock->expects($this->once())
            ->method('result')
            ->willReturn([]);

        $this->runPrivateMethod($this->app, 'handleBenchmarks', [['test' => $mock]]);
    }

    public function testBenchmarkCompletedUpdatesTotalTime()
    {
        $stub = $this->getMockBuilder(Integers::class)
            ->getMock();
        $stub->expects($this->once())
            ->method('result')
            ->willReturn(['exec_time' => 42]);

        $this->runPrivateMethod($this->app, 'handleBenchmarks', [['test' => $stub]]);

        $this->assertContains('42', $this->app->getStatistics(['total_time']));
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithoutAdditionalInformation()
    {
        $withoutAdditionalInformation = ['exec_time' => 42];
        $result = $this->runPrivateMethod($this->app, 'generateDefaultReport', ['test', $withoutAdditionalInformation]);

        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithAdditionalInformation()
    {
        $withAdditionalInformation = ['exec_time' => 42, 'write_speed' => 32, 'read_speed' => 16, 'some_time' => 8];
        $result = $this->runPrivateMethod($this->app, 'generateDefaultReport', ['test', $withAdditionalInformation]);

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
        $this->assertArrayHasKey('write_speed', $result);
        $this->assertEquals(32, $result['write_speed']);
        $this->assertArrayNotHasKey('some_time', $result);
    }

    /**
     * @param array $input
     * @param string $expected
     * @throws \ReflectionException
     * @dataProvider provideFormatExecution
     */
    public function testFormatExecutionTimeReturnsExpected(array $input, $expected)
    {
        $result = $this->runPrivateMethod($this->app, 'formatExecutionTime', $input);

        $this->assertEquals($expected, $result);
    }

    public function provideFormatExecution()
    {
        return [
            'When input is string' => [['test'], 'test'],
            'When is int and precision 0' => [[1, 0], '1s'],
            'When is int and precision 2' => [[1, 2], '1.00s'],
            'When is float and precision 2' => [[2.729513, 2], '2.72s'],
            'When is float and precision 3 with rounding' => [[2.729513, 3], '2.729s'],
            'When is float and precision 3 with trailing zero' => [[2.720513, 3, 3], '2.720s'],
            'When is float and precision 11' => [[2.7684543132782, 11], '2.76845431327s'],
            'When is float and precision 12' => [[2.7684543132782, 12], '2.768454313278s'],
            'When is float and precision 13 to default length' => [[2.7684543132782, 13], '2.768s'],
        ];
    }

    public function testFormatExecutionTimeBatchReturns()
    {
        $statistics = [
            'read_time' => 2.7684543132782,
            'exec_time' => 3.15918564796448,
            'untouchable' => 1.5,
        ];

        $result = $this->runPrivateMethod($this->app, 'formatExecutionTimeBatch', [$statistics]);

        $this->assertCount(3, $result);
        $this->assertEquals('2.768s', $result['read_time']);
        $this->assertEquals('3.159s', $result['exec_time']);
        $this->assertEquals(1.5, $result['untouchable']);
    }

    /**
     * @param array $input
     * @param bool $expected
     * @throws \ReflectionException
     * @dataProvider provideIsValidPrecision
     */
    public function testIsValidPrecisionReturnsExpected(array $input, $expected)
    {
        $result = $this->runPrivateMethod($this->app, 'isValidPrecision', $input);

        $this->assertSame($expected, $result);
    }

    public function provideIsValidPrecision()
    {
        return [
            'When less than valid' => [[-1], false],
            'When min valid precision' => [[0], true],
            'When max valid precision' => [[12], true],
            'When more than valid' => [[13], false],
            'When not an integer' => [[null], false],

        ];
    }

    public function testGetStatisticsReturnsFullStatisticsWhenEmptyKeys()
    {
        $statistics = $this->app->getStatistics();

        $this->assertInternalType('array', $statistics);
        $this->assertArrayHasKey('total_time', $statistics);
    }

    public function testGetStatisticsReturnsEmptyArrayWhenKeyDoesNotExist()
    {
        $statistics = $this->app->getStatistics(['not_exist']);

        $this->assertEmpty($statistics);
    }

    public function testGetStatisticsReturnsExpectedResultWhenTwoKeysExist()
    {
        $statistics = $this->app->getStatistics(['started_at', 'stopped_at']);

        $this->assertCount(2, $statistics);
        $this->assertArrayHasKey('started_at', $statistics);
        $this->assertArrayHasKey('stopped_at', $statistics);
    }

    public function testGetStatisticsReturnsExpectedResultOrder()
    {
        /**
         * Here we fake private variable to be sure in internal data.
         */
        $fake_statistics = [
            'completed' => 0,
            'skipped' => 0,
        ];

        $this->setPrivateVariableValue($this->app, 'statistics', $fake_statistics);

        $orderCompletedFirst = [
            'completed' => 0,
            'skipped' => 0,
        ];

        $this->assertSame($orderCompletedFirst, $this->app->getStatistics(['completed', 'skipped']));

        $orderSkippedFirst = [
            'skipped' => 0,
            'completed' => 0,
        ];

        $this->assertSame($orderSkippedFirst, $this->app->getStatistics(['skipped', 'completed']));
    }

    public function testGetStatisticsForHumans()
    {
        $statistics = $this->app->getStatisticsForHumans(['started_at', 'stopped_at']);

        $this->assertCount(2, $statistics);
        $this->assertArrayHasKey('Started at', $statistics);
        $this->assertArrayHasKey('Stopped at', $statistics);
    }

    public function testGetBenchmarksSummaryReturnsExpectedWhenEmptyBenchmarks()
    {
        /**
         * Here we fake private variable to be sure in internal data.
         */
        $fake_statistics = [
            'completed' => 0,
            'skipped' => 0,
            'total_time' => 0,
        ];

        $this->setPrivateVariableValue($this->app, 'statistics', $fake_statistics);

        $result = $this->app->getBenchmarksSummary();

        $this->assertArrayHasKey('skip', $result);
    }

    public function testIsSilentModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['debug' => false, 'verbose' => false]);

        $result = $this->runPrivateMethod($this->app, 'isSilentMode');

        $this->assertTrue($result);
    }

    public function testIsSilentModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['debug' => true, 'verbose' => false]);

        $result = $this->runPrivateMethod($this->app, 'isSilentMode');

        $this->assertFalse($result);
    }

    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['debug' => true]);

        $result = $this->runPrivateMethod($this->app, 'isDebugMode');

        $this->assertTrue($result);
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['debug' => false]);

        $result = $this->runPrivateMethod($this->app, 'isDebugMode');

        $this->assertFalse($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['verbose' => true]);

        $result = $this->runPrivateMethod($this->app, 'isVerboseMode');

        $this->assertTrue($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->app, 'options', ['verbose' => false]);

        $result = $this->runPrivateMethod($this->app, 'isVerboseMode');

        $this->assertFalse($result);
    }

    /**
     * Helpers.
     */

    /**
     * @throws \ReflectionException
     * @return Application|object
     */
    protected function getInstanceWithSkippedConstructor()
    {
        $reflection = new \ReflectionClass(Application::class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
