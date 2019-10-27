<?php

namespace BenchmarkPHP\Tests;

use BenchmarkPHP\Benchmark;
use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Reporters\Reporter;
use BenchmarkPHP\Benchmarks\MathIntegers;
use BenchmarkPHP\Benchmarks\AbstractBenchmark;

class BenchmarkTest extends TestCase
{
    use TestHelpers;

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
    public function testInitOptionsReturnsExpectedWhenOnlyOneArgument()
    {
        $arguments = [array_shift($_SERVER['argv'])];

        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $method = $this->getPrivateMethod($partialMock, 'initOptions');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('verbose', $result);
    }

    public function testInitOptionsReturnsExpectedWhenArgumentsContainOption()
    {
        $arguments = array_merge($_SERVER['argv'], ['--verbose']);

        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $method = $this->getPrivateMethod($partialMock, 'initOptions');
        $result = $method->invokeArgs($partialMock, [$arguments]);

        $this->assertContains('verbose', $result);
    }

    public function testInitOptionsReturnsExpectedWhenArgumentsContainVersion()
    {
        $arguments = array_merge($_SERVER['argv'], ['--version']);

        $reporter = $this->getMockBuilder(Reporter::class)
            ->getMock();
        $partialMock = $this->getMockBuilder(Benchmark::class)
            ->setConstructorArgs([$reporter])
            ->setMethods(['terminateWithCode'])
            ->getMock();
        $partialMock->expects($this->once())
            ->method('terminateWithCode')
            ->willReturn(true);

        $method = $this->getPrivateMethod($partialMock, 'initOptions');
        $method->invokeArgs($partialMock, [$arguments]);
    }

    public function testInitBenchmarksReturnExpected()
    {
        $count = null;
        // count declared classes only with specific namespace
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, AbstractBenchmark::class) && strpos($class, 'BenchmarkPHP\Benchmarks\\') !== false) {
                ++$count;
            }
        }

        $method = $this->getPrivateMethod($this->bench, 'initBenchmarks');

        $benchmarks = $method->invoke($this->bench);
        $this->assertInternalType('array', $benchmarks);
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
        $mock = $this->getMockBuilder(MathIntegers::class)
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

    public function testBenchmarkCompletedUpdateTotalTime()
    {
        $stub = $this->getMockBuilder(MathIntegers::class)
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

    public function testGetStatisticsReturnsFullStatisticsWhenEmptyKeys()
    {
        $statistics = $this->bench->getStatistics();

        $this->assertInternalType('array', $statistics);
        $this->assertArrayHasKey('total_time', $statistics);
    }

    public function testGetStatisticsReturnsEmptyArrayWhenKeyNotExists()
    {
        $statistics = $this->bench->getStatistics(['not_exists']);

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

    public function testGetHandleStatisticsReturnsExpectedOnEmptyBenchmarks()
    {
        $this->setPrivateVariableValue($this->bench, 'benchmarks', []);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $handled = $this->bench->getHandleStatistics();

        $this->assertContains('0', $handled['done']);
    }

    public function testGetHandleStatisticsReturnsExpectedOnOneCompletedBenchmark()
    {
        $stub = $this->getMockBuilder(MathIntegers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stub->expects($this->any())
            ->method('result')
            ->willReturn([]);
        $this->setPrivateVariableValue($this->bench, 'benchmarks', ['test' => $stub]);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $handled = $this->bench->getHandleStatistics();

        $this->assertContains('1', $handled['done']);
    }

    public function testGetHandleStatisticsRerturnsExpectedOnSkippedBenchmark()
    {
        $skipped = ['test' => 'skipped'];
        $this->setPrivateVariableValue($this->bench, 'benchmarks', [$skipped]);

        $method = $this->getPrivateMethod($this->bench, 'handleBenchmarks');
        $method->invoke($this->bench);
        $handled = $this->bench->getHandleStatistics();

        $this->assertContains('0', $handled['done']);
        $this->assertContains('1', $handled['skip']);
    }

    public function testGenerateClassNameReturnExpectedWhenOneWord()
    {
        $expected = 'Strings';
        $method = $this->getPrivateMethod($this->bench, 'generateClassName');

        $this->assertEquals($expected, $method->invokeArgs($this->bench, ['strings']));
    }

    public function testGenerateClassNameReturnExpectedWhenMultiWords()
    {
        $expected = 'MathIntegers';
        $method = $this->getPrivateMethod($this->bench, 'generateClassName');

        $this->assertEquals($expected, $method->invokeArgs($this->bench, ['math_integers']));
    }

    public function testGenerateBenchmarkCountReturnsExpectedWhenOneResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateBenchmarkCount');

        $this->assertEquals('1 benchmark', $method->invokeArgs($this->bench, [1]));
    }

    public function testGenerateBenchmarkCountReturnsExpectedWhenThreeResult()
    {
        $method = $this->getPrivateMethod($this->bench, 'generateBenchmarkCount');

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
}
