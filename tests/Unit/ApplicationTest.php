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
use BenchmarkPHP\Arguments\CliHandler;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Reporters\ReporterInterface;
use BenchmarkPHP\Benchmarks\Benchmarks\Integers;
use BenchmarkPHP\Arguments\ArgumentsHandlerInterface;
use BenchmarkPHP\Benchmarks\Benchmarks\AbstractBenchmark;

class ApplicationTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Application */
    private $bench;

    protected function setUp()
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = ['-a'];

        /** @var ArgumentsHandlerInterface|\PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = new CliHandler([]); // todo remove
        /** @var ReporterInterface|\PHPUnit_Framework_MockObject_MockObject $reporter */
        $reporter = $this->getMockBuilder(ReporterInterface::class)
            ->getMock();
        $this->bench = new Application($_SERVER['argv'], $handler, $reporter);
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
    public function testInitBenchmarksReturnsExpected()
    {
        $count = $this->countAbstractBenchmarkClasses();

        $benchmarks = $this->runPrivateMethod($this->bench, 'initBenchmarks');

        $this->assertInternalType('array', $benchmarks);
        $this->assertCount($count, $benchmarks);
    }

    public function testInitBenchmarksPassesOptionsToBenchmarkInstance()
    {
        $options = ['verbose' => 'updated'];

        $this->setPrivateVariableValue($this->bench, 'options', $options);
        $result = $this->runPrivateMethod($this->bench, 'initBenchmarks', [$options]);

        $instance = current($result);
        $this->assertInstanceOf(AbstractBenchmark::class, $instance);
        $this->assertEquals($options, $instance->getOptions());
    }

    public function testHandleBenchmarksExecutesBeforeHandle()
    {
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $this->runPrivateMethod($this->bench, 'handleBenchmarks');

        $this->assertContains(date(Application::DATE_FORMAT), $this->bench->getStatistics(['started_at']));
    }

    public function testHandleBenchmarksExecutesAfterHandle()
    {
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $this->runPrivateMethod($this->bench, 'handleBenchmarks');

        $this->assertContains(date(Application::DATE_FORMAT), $this->bench->getStatistics(['stopped_at']));
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

        $this->setPrivateVariableValue($this->bench, 'benchmarks', ['test' => $mock]);
        $this->runPrivateMethod($this->bench, 'handleBenchmarks');
    }

    public function testBenchmarkCompletedUpdatesTotalTime()
    {
        $stub = $this->getMockBuilder(Integers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stub->expects($this->once())
            ->method('result')
            ->willReturn(['exec_time' => 42]);

        $this->setPrivateVariableValue($this->bench, 'benchmarks', ['test' => $stub]);
        $this->runPrivateMethod($this->bench, 'handleBenchmarks');

        $this->assertContains('42', $this->bench->getStatistics(['total_time']));
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithoutAdditionalInformation()
    {
        $result = $this->runPrivateMethod($this->bench, 'generateDefaultReport', ['test', ['exec_time' => 42]]);

        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithAdditionalInformation()
    {
        $result = $this->runPrivateMethod(
            $this->bench,
            'generateDefaultReport',
            ['test', ['exec_time' => 42, 'write_speed' => 32, 'read_speed' => 16, 'some_time' => 8]]
        );

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
        $this->assertArrayHasKey('write_speed', $result);
        $this->assertEquals(32, $result['write_speed']);
        $this->assertArrayNotHasKey('some_time', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenString()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', ['test']);

        $this->assertEquals('test', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsIntAndPrecisionIs0()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [1, 0]);

        $this->assertEquals('1s', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsIntAndPrecisionIs2()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [1, 2]);

        $this->assertEquals('1.00s', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsFloatAndPrecisionIs2()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.729513, 2]);

        $this->assertEquals('2.72s', $result);
    }

    public function testFormatExecutionTimeReturnsWithoutRoundingWhenTimeAndPrecisionIs3()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.729513, 3]);

        $this->assertEquals('2.729s', $result);
    }

    public function testFormatExecutionTimeReturnsWithTrailingZeroWhenTimeAndPrecisionIs3()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.720513, 3]);

        $this->assertEquals('2.720s', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsFloatAndPrecisionIs10()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.7684543132782, 10]);

        $this->assertEquals('2.7684543132s', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsFloatAndPrecisionIs12()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.7684543132782, 12]);

        $this->assertEquals('2.768454313278s', $result);
    }

    public function testFormatExecutionTimeReturnsExpectedWhenTimeIsFloatAndPrecisionIs13OutOfBoundary()
    {
        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTime', [2.7684543132782, 13]);

        $this->assertEquals('2.768s', $result);
    }

    public function testFormatExecutionTimeBatchReturns()
    {
        $statistics = [
            'read_time' => 2.7684543132782,
            'exec_time' => 3.15918564796448,
            'untouchable' => 1.5,
        ];

        $result = $this->runPrivateMethod($this->bench, 'formatExecutionTimeBatch', [$statistics]);

        $this->assertCount(3, $result);
        $this->assertEquals('2.768s', $result['read_time']);
        $this->assertEquals('3.159s', $result['exec_time']);
        $this->assertEquals(1.5, $result['untouchable']);
    }

    public function testIsValidPrecisionReturnsExpectedWhenValidPrecision()
    {
        $result = $this->runPrivateMethod($this->bench, 'isValidPrecision', [0]);

        $this->assertTrue($result);
    }

    public function testIsValidPrecisionReturnsExpectedWhenNotAnInteger()
    {
        $result = $this->runPrivateMethod($this->bench, 'isValidPrecision', [null]);

        $this->assertFalse($result);
    }

    public function testIsValidPrecisionReturnsExpectedWhenGreaterThan3()
    {
        $result = $this->runPrivateMethod($this->bench, 'isValidPrecision', [14]);

        $this->assertFalse($result);
    }

    public function testGetStatisticsReturnsFullStatisticsWhenEmptyKeys()
    {
        $statistics = $this->bench->getStatistics();

        $this->assertInternalType('array', $statistics);
        $this->assertArrayHasKey('total_time', $statistics);
    }

    public function testGetStatisticsReturnsEmptyArrayWhenKeyDoesNotExist()
    {
        $statistics = $this->bench->getStatistics(['not_exist']);

        $this->assertEmpty($statistics);
    }

    public function testGetStatisticsReturnsExpectedResultWhenTwoKeysExist()
    {
        $statistics = $this->bench->getStatistics(['started_at', 'stopped_at']);

        $this->assertCount(2, $statistics);
        $this->assertArrayHasKey('started_at', $statistics);
        $this->assertArrayHasKey('stopped_at', $statistics);
    }

    public function testGetStatisticsReturnsExpectedResultOrder()
    {
        $orderCompletedFirst = [
            'completed' => 0,
            'skipped' => 0,
        ];

        $this->setPrivateVariableValue($this->bench, 'statistics', $orderCompletedFirst);
        $this->assertSame($orderCompletedFirst, $this->bench->getStatistics(['completed', 'skipped']));

        $orderSkippedFirst = [
            'skipped' => 0,
            'completed' => 0,
        ];

        $this->setPrivateVariableValue($this->bench, 'statistics', $orderSkippedFirst);
        $this->assertSame($orderCompletedFirst, $this->bench->getStatistics(['completed', 'skipped']));
    }

    public function testGetStatisticsForHumans()
    {
        $statistics = $this->bench->getStatisticsForHumans(['started_at', 'stopped_at']);

        $this->assertCount(2, $statistics);
        $this->assertArrayHasKey('Started at', $statistics);
        $this->assertArrayHasKey('Stopped at', $statistics);
    }

    public function testGetBenchmarksSummaryReturnsExpectedWhenEmptyBenchmarks()
    {
        $fake_statistics = [
            'completed' => 0,
            'skipped' => 0,
            'total_time' => 0,
        ];

        $this->setPrivateVariableValue($this->bench, 'statistics', $fake_statistics);

        $result = $this->bench->getBenchmarksSummary();

        $this->assertArrayHasKey('skip', $result);
    }

    public function testIsSilentModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false, 'verbose' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isSilentMode');

        $this->assertTrue($result);
    }

    public function testIsSilentModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true, 'verbose' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isSilentMode');

        $this->assertFalse($result);
    }

    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);

        $result = $this->runPrivateMethod($this->bench, 'isDebugMode');

        $this->assertTrue($result);
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isDebugMode');

        $this->assertFalse($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $result = $this->runPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertTrue($result);
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => false]);

        $result = $this->runPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertFalse($result);
    }

    /**
     * Test exits benchmark.
     */

    /**
     * @dataProvider provideParseArgumentsData
     * @param array $arguments
     * @param string $method
     * @param string $verify
     * @param string $message
     * @throws \ReflectionException
     */
    public function testParseArgumentsExecutesTerminateMethod($arguments, $method, $verify, $message)
    {
        $reporter = $this->getMockBuilder(ReporterInterface::class)
            ->getMock();
        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->setConstructorArgs([$reporter])
            ->setMethods([$method])
            ->getMock();
        $partialMock->expects($this->once())
            ->method($method)
            ->with($this->stringContains($verify))
            ->will($this->throwException(new \InvalidArgumentException($message)));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        $this->runPrivateMethod($partialMock, 'parseArguments', [$arguments]);
    }

    public function provideParseArgumentsData()
    {
        return [
            'When empty arguments returns help message' => [
                [],
                'terminateWithCode',
                0,
                'Return help message.',
            ],
            'When option does not exist returns error message' => [
                ['--not_exist' => false],
                'terminateWithMessage',
                'unknown',
                'Option doesn\'t exist.',
            ],
            'When option exists and should returns something' => [
                ['--version' => false],
                'terminateWithCode',
                0,
                'Option should terminate execution.',
            ],
            'When all and benchmarks option in the same time returns error message' => [
                ['-a' => false, '-b' => 'integers'],
                'terminateWithMessage',
                'wrong',
                'Wrong options combination.',
            ],
            'When exclude option lacks all option returns error message' => [
                ['-v' => false, '-e' => 'integers'],
                'terminateWithMessage',
                'wrong',
                'Wrong options combination.',
            ],
            'When exclude option\'s value contains undefined benchmark returns error message' => [
                ['-a' => false, '-e' => 'test,integers,not_exist'],
                'terminateWithMessage',
                'test,not_exist',
                'Wrong benchmarks names passed.',
            ],
            'When benchmarks option\'s value is wrong returns error message' => [
                ['-b' => false],
                'terminateWithMessage',
                'wrong',
                'Wrong value passed.',
            ],
            'When benchmarks option\'s value contains undefined benchmark returns error message' => [
                ['-b' => 'test,integers,not_exist'],
                'terminateWithMessage',
                'test,not_exist',
                'Wrong benchmarks names passed.',
            ],
            'When iterations option\'s value is wrong returns error message' => [
                ['-i' => 'x'],
                'terminateWithMessage',
                'wrong',
                'Wrong value passed.',
            ],
            'When iterations option\'s value is out of range returns error message' => [
                ['-i' => '0'],
                'terminateWithMessage',
                'between',
                'Value out of range passed.',
            ],
            'When precision option\'s value is wrong returns error message' => [
                ['--data-precision' => 'x'],
                'terminateWithMessage',
                'wrong',
                'Wrong value passed.',
            ],
            'When precision option\'s value is out of range returns error message' => [
                ['--data-precision' => '-1'],
                'terminateWithMessage',
                'positive',
                'Value is not positive numeric.',
            ],
        ];
    }

    /**
     * Captured bugs.
     */

    /**
     * @dataProvider provideRequireValueConst
     * @param array $arguments
     * @param string $message
     * @throws \ReflectionException
     */
    public function testRequireValueConstContainsOnlyRequireValueOptions($arguments, $message)
    {
        $reporter = $this->getMockBuilder(ReporterInterface::class)
            ->getMock();
        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->setConstructorArgs([$reporter])
            ->setMethods(['terminateWithMessage'])
            ->getMock();
        $partialMock->expects($this->once())
            ->method('terminateWithMessage')
            ->with($this->stringContains('requires'))
            ->will($this->throwException(new \InvalidArgumentException($message)));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        $this->runPrivateMethod($partialMock, 'parseArguments', [$arguments]);
    }

    public function provideRequireValueConst()
    {
        $require = [];
        $pickyOptions = [
            '-e' => ['-a' => false, '-e' => false],
            '--exclude' => ['-a' => false, '--exclude' => false],
        ];

        foreach (Application::REQUIRE_VALUE_ARGUMENTS as $option) {
            $arguments = array_key_exists($option, $pickyOptions) ? $pickyOptions[$option] : [$option => false];
            $require['When required value for option ' . $option . ' is missed'] = [
                $arguments,
                'Option ' . $option . ' requires the value.',
            ];
        }

        return $require;
    }

    /**
     * Helpers.
     */

    /**
     * @return Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartialMockWithSkippedConstructor()
    {
        return $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    /**
     * @return int|null
     */
    private function countAbstractBenchmarkClasses()
    {
        $count = null;
        // count declared classes only with specific namespace
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, AbstractBenchmark::class) && strpos(
                $class,
                'BenchmarkPHP\Benchmarks\\'
            ) !== false) {
                ++$count;
            }
        }

        return $count;
    }
}
