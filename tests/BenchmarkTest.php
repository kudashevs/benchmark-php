<?php

namespace BenchmarkPHP\Tests;

use BenchmarkPHP\Benchmark;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Reporters\Reporter;
use BenchmarkPHP\Benchmarks\Integers;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class BenchmarkTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Benchmark */
    private $bench;

    protected function setUp()
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = [array_shift($_SERVER['argv'])];

        /** @var Reporter|\PHPUnit_Framework_MockObject_MockObject $reporter */
        $reporter = $this->getMockBuilder(Reporter::class)
            ->getMock();
        $this->bench = new Benchmark($reporter);
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
    public function testInitArgumentsReturnsEmptyArrayWhenOneArgument()
    {
        $arguments = [array_shift($_SERVER['argv'])];

        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $method = $this->getPrivateMethod($partialMock, 'initArguments');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testInitArgumentsReturnsExpectedWhenMixedArguments()
    {
        $arguments = array_merge([array_shift($_SERVER['argv'])], ['-b', 'test', '-x', '--debug']);
        $expected = [
            '-b' => 'test',
            '-x' => false,
            '--debug' => false,
        ];

        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $method = $this->getPrivateMethod($partialMock, 'initArguments');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertEquals($expected, $result);
    }

    public function testParseArgumentsReturnsDefaultOptionsWhenEmptyArray()
    {
        $arguments = [];

        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $method = $this->getPrivateMethod($partialMock, 'parseArguments');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('verbose', $result);
    }

    public function testParseArgumentsReturnsExpectedWhenArgumentIsAnOption()
    {
        $arguments = ['--verbose' => false];

        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $method = $this->getPrivateMethod($partialMock, 'parseArguments');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertContains('verbose', $result);
    }

    public function testParseRequiredArgumentIsBenchmarkNameReturnsExpectedWhenTwoExistedBenchmarksInValue()
    {
        $argument = '-b';
        $value = 'integers,floats';

        $method = $this->getPrivateMethod($this->bench, 'parseRequiredArgumentIsBenchmarkName');
        $result = $method->invokeArgs($this->bench, [$argument, $value]);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContains('floats', $result);
    }

    public function testParseRequiredArgumentIsPositiveIntegerReturnsExpectedWhenCorrectInteger()
    {
        $argument = '-i';
        $value = '42';

        $method = $this->getPrivateMethod($this->bench, 'parseRequiredArgumentIsPositiveInteger');
        $result = $method->invokeArgs($this->bench, [$argument, $value]);

        $this->assertSame(42, $result);
    }

    public function testParseRequiredArgumentIsFilenameReturnsExpectedWhenCorrectFilename()
    {
        $argument = '--temporary-file';
        $value = 'test.txt';

        $method = $this->getPrivateMethod($this->bench, 'parseRequiredArgumentIsFilename');
        $result = $method->invokeArgs($this->bench, [$argument, $value]);

        $this->assertSame('test.txt', $result);
    }

    public function testGeneratePrintableWithSpaceReturnsStringWhenValueIsPrintable()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePrintableWithSpace');
        $result = $method->invokeArgs($this->bench, [42]);

        $this->assertSame('42 ', $result);
    }

    public function testGeneratePrintableWithSpaceReturnsSpaceStringWhenValueIsNotPrintable()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePrintableWithSpace');
        $result = $method->invokeArgs($this->bench, [[42]]);

        $this->assertSame(' ', $result);
    }

    public function testInitBenchmarksReturnsExpected()
    {
        $count = $this->countAbstractBenchmarkClasses();

        $method = $this->getPrivateMethod($this->bench, 'initBenchmarks');
        $benchmarks = $method->invoke($this->bench);

        $this->assertInternalType('array', $benchmarks);
        $this->assertCount($count, $benchmarks);
    }

    public function testInitBenchmarksPassesOptionsToBenchmarkInstance()
    {
        $options = ['verbose' => 'updated'];

        $this->setPrivateVariableValue($this->bench, 'options', $options);
        $method = $this->getPrivateMethod($this->bench, 'initBenchmarks');
        $result = $method->invoke($this->bench);

        $instance = current($result);
        $this->assertInstanceOf(AbstractBenchmark::class, $instance);
        $this->assertEquals($options, $instance->getOptions());
    }

    public function testListBenchmarksReturnsExpected()
    {
        $count = $this->countAbstractBenchmarkClasses();

        $method = $this->getPrivateMethod($this->bench, 'listBenchmarks');
        $benchmarks = $method->invoke($this->bench);

        $this->assertNotEmpty($benchmarks);
        $this->assertContainsOnly('string', $benchmarks);
        $this->assertCount($count, $benchmarks);
    }

    public function testHandleBenchmarksExecutesBeforeHandle()
    {
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);

        $this->assertContains(date(Benchmark::DATE_FORMAT), $this->bench->getStatistics(['started_at']));
    }

    public function testHandleBenchmarksExecutesAfterHandle()
    {
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);

        $this->assertContains(date(Benchmark::DATE_FORMAT), $this->bench->getStatistics(['stopped_at']));
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

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
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

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);

        $this->assertContains('42', $this->bench->getStatistics(['total_time']));
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithoutAdditionalInformation()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultReport');
        $result = $method->invokeArgs($this->bench, ['test', ['exec_time' => 42]]);

        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
    }

    public function testGenerateDefaultReportReturnsExpectedWhenWithAdditionalInformation()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultReport');
        $result = $method->invokeArgs($this->bench, ['test', ['exec_time' => 42, 'write_speed' => 32, 'read_speed' => 16, 'some_time' => 8]]);

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertStringStartsWith('42', $result['test']);
        $this->assertArrayHasKey('write_speed', $result);
        $this->assertEquals(32, $result['write_speed']);
        $this->assertArrayNotHasKey('some_time', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsExpectedWhenString()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, ['test']);

        $this->assertEquals('test', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsExpectedWhenTimeIsIntAndPrecisionIs0()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [1, 0]);

        $this->assertEquals('1s', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsExpectedWhenTimeIsIntAndPrecisionIs2()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [1, 2]);

        $this->assertEquals('1.00s', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsExpectedWhenTimeIsFloatAndPrecisionIs2()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [2.729513, 2]);

        $this->assertEquals('2.72s', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsWithoutRoundingWhenTimeAndPrecisionIs3()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [2.729513, 3]);

        $this->assertEquals('2.729s', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsWithTrailingZeroWhenTimeAndPrecisionIs3()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [2.720513, 3]);

        $this->assertEquals('2.720s', $result);
    }

    public function testGenerateDefaultExecutionTimeReturnsWhenTimeIsFloatAndPrecisionIs10()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateDefaultExecutionTime');
        $result = $method->invokeArgs($this->bench, [42.7684543132782, 10]);

        $this->assertEquals('42.7684543132s', $result);
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
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $result = $this->bench->getBenchmarksSummary();

        $this->assertArrayHasKey('skip', $result);
    }

    public function testGetBenchmarksSummaryReturnsExpectedWhenOneCompletedBenchmark()
    {
        $stub = $this->getMockBuilder(Integers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stub->expects($this->once())
            ->method('result')
            ->willReturn([]);
        $this->setPrivateVariableValue($this->bench, 'benchmarks', ['test' => $stub]);
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $result = $this->bench->getBenchmarksSummary();

        $this->assertContains('1', $result['done']);
    }

    public function testGetBenchmarksSummaryReturnsExpectedWhenSkippedBenchmark()
    {
        $skipped = ['test' => 'skipped'];
        $this->setPrivateVariableValue($this->bench, 'benchmarks', [$skipped]);
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $result = $this->bench->getBenchmarksSummary();

        $this->assertContains('0', $result['done']);
        $this->assertContains('1', $result['skip']);
    }

    public function testIsSilentModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false, 'verbose' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isSilentMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsSilentModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true, 'verbose' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isSilentMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testIsDebugModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsDebugModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['debug' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isDebugMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnsExpectedWhenTrue()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => true]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertTrue($method->invoke($this->bench));
    }

    public function testIsVerboseModeReturnsExpectedWhenFalse()
    {
        $this->setPrivateVariableValue($this->bench, 'options', ['verbose' => false]);

        $method = $this->getPrivateMethod($this->bench, 'isVerboseMode');

        $this->assertFalse($method->invoke($this->bench));
    }

    public function testGeneratePluralizedBenchmarkCountReturnsExpectedWhenZeroResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedBenchmarkCount');

        $this->assertEquals('0 benchmarks', $method->invokeArgs($this->bench, [0]));
    }

    public function testGeneratePluralizedBenchmarkCountReturnsExpectedWhenOneResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedBenchmarkCount');

        $this->assertEquals('1 benchmark', $method->invokeArgs($this->bench, [1]));
    }

    public function testGeneratePluralizedBenchmarkCountReturnsExpectedWhenThreeResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generatePluralizedBenchmarkCount');

        $this->assertEquals('3 benchmarks', $method->invokeArgs($this->bench, [3]));
    }

    public function testGetSystemInformationReturnsExpected() // refactor
    {
        $host = gethostname();
        $version = PHP_VERSION;
        $os = PHP_OS;
        $platform = php_uname('m');

        $information = $this->bench->getSystemInformation();
        $this->assertContains($host, $information['Server']);
        $this->assertContains($version, $information);
        $this->assertContains($os, $information['Platform']);
        $this->assertContains($platform, $information['Platform']);
    }

    /**
     * Test exits benchmark.
     */

    /**
     * @dataProvider provideInitArgumentsData
     * @param array $arguments
     * @param array $required
     * @param string $verify
     * @param string $message
     * @throws \ReflectionException
     */
    public function testInitArgumentsExecutesTerminateMethod($arguments, $required, $verify, $message)
    {
        $reporter = $this->getMockBuilder(Reporter::class)
            ->getMock();
        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->setConstructorArgs([$reporter])
            ->setMethods(['terminateWithMessage'])
            ->getMock();
        $partialMock->expects($this->once())
            ->method('terminateWithMessage')
            ->with($this->stringContains($verify))
            ->will($this->throwException(new \InvalidArgumentException($message)));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        $method = $this->getPrivateMethod($partialMock, 'initArguments');
        $method->invokeArgs($partialMock, [$arguments, $required]);
    }

    public function provideInitArgumentsData()
    {
        return [
            'When required option value is missed' => [
                array_merge($_SERVER['argv'], ['-b']),
                ['-b'],
                'empty',
                'Passed value is empty.',
            ],
            'When required option value looks like another option' => [
                array_merge($_SERVER['argv'], ['-b', '-c']),
                ['-b'],
                'wrong',
                'Passed value looks like option.',
            ],
            'When one of required option values looks like another option' => [
                array_merge($_SERVER['argv'], ['-c', 'path', '--benchmarks', '--debug']),
                ['-c', '--benchmarks'],
                'wrong',
                'One of passed values looks like option.',
            ],

        ];
    }

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
        $reporter = $this->getMockBuilder(Reporter::class)
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
        $method = $this->getPrivateMethod($partialMock, 'parseArguments');
        $method->invokeArgs($partialMock, [$arguments]);
    }

    public function provideParseArgumentsData()
    {
        return [
            'When option does not exist' => [
                ['--not_exist' => false],
                'terminateWithMessage',
                'unknown',
                'Option doesn\'t exist.',
            ],
            'When option exists and should terminate execution' => [
                ['--version' => false],
                'terminateWithCode',
                0,
                'Option should terminate execution.',
            ],
            'When benchmarks option\'s value is wrong' => [
                ['-b' => false],
                'terminateWithMessage',
                'wrong',
                'Wrong value passed.',
            ],
            'When benchmarks option\'s value contains undefined benchmark' => [
                ['-b' => 'test,integers,not_exist'],
                'terminateWithMessage',
                'test,not_exist',
                'Wrong benchmarks names passed.',
            ],
            'When iterations option\'s value is wrong' => [
                ['-i' => 'x'],
                'terminateWithMessage',
                'wrong',
                'Wrong value passed.',
            ],
            'When iterations option\'s value is out of range' => [
                ['-i' => '-1'],
                'terminateWithMessage',
                'between',
                'Value out of range passed.',
            ],
        ];
    }

    /**
     * Helpers.
     */

    /**
     * @return Benchmark|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartialMockWithSkippedConstructor()
    {
        return $this->getMockBuilder(Benchmark::class)
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
