<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Arguments;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Arguments\CliArgumentsHandler;
use BenchmarkPHP\Exceptions\EmptyArgumentException;
use BenchmarkPHP\Exceptions\WrongArgumentException;
use BenchmarkPHP\Arguments\ArgumentsHandlerInterface;
use BenchmarkPHP\Exceptions\UnknownArgumentException;

class CliArgumentsHandlerTest extends TestCase
{
    /** @var CliArgumentsHandler */
    private $handler;

    protected function setUp()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = [array_shift($_SERVER['argv']), '-a'];

        $this->handler = new CliArgumentsHandler();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(ArgumentsHandlerInterface::class, $this->handler);
    }

    /**
     * Exceptions.
     */
    public function testHandleThrowExceptionWhenRequiredArgumentDoesntHaveValue()
    {
        $require = current(CliArgumentsHandler::REQUIRE_VALUE_ARGUMENTS);

        $this->expectException(EmptyArgumentException::class);
        $this->expectExceptionMessage('Empty value');

        $this->handler->parse([$require]);
    }

    public function testHandleThrowExceptionWhenRequiredArgumentIsLikeAnOption()
    {
        $require = current(CliArgumentsHandler::REQUIRE_VALUE_ARGUMENTS);
        $value = '-c';

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage('Wrong value ' . $value);

        $this->handler->parse([$require, $value]);
    }

    public function testHandleThrowExceptionWhenArgumentsWithUnknownOption()
    {
        $arguments = ['-x'];

        $this->expectException(UnknownArgumentException::class);
        $this->expectExceptionMessage('Unknown option ' . $arguments[0]);

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenArgumentsWithoutInclusive()
    {
        $arguments = ['-e', 42, '-l', '-b', 42];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' is mutually inclusive');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenArgumentsWithExclusive()
    {
        $arguments = ['-a', '-l', '-b', 42];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' is mutually exclusive');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenBenchmarkWithWrongType()
    {
        $arguments = ['-b', 42];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires a benchmark');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenBenchmarkDoesNotExist()
    {
        $arguments = ['-b', 'not_exist'];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires a valid benchmark');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenIterationWithWrongType()
    {
        $arguments = ['-i', 'wrong'];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires a number of iterations');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenIterationWithWrongRange()
    {
        $arguments = ['-i', 0];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires the value between');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenPrecisionWithWrongType()
    {
        $arguments = ['--time-precision', 'wrong'];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires a numeric value');

        $this->handler->parse($arguments);
    }

    public function testHandleThrowExceptionWhenFilenameWithWrongType()
    {
        $arguments = ['--temporary-file', 42];

        $this->expectException(WrongArgumentException::class);
        $this->expectExceptionMessage($arguments[0] . ' requires a filename');

        $this->handler->parse($arguments);
    }

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testHandleReturnsExpectedWhenEmptyArguments()
    {
        $expected = ['action' => '', 'options' => []];

        $result = $this->handler->parse([]);

        $this->assertSame($expected, $result);
    }

    public function testHandleReturnsExpectedWhenArgumentIsAShortOption()
    {
        $arguments = ['-v'];

        $result = $this->handler->parse($arguments);

        $this->assertArrayHasKey('verbose', $result['options']);
    }

    public function testHandleReturnsExpectedWhenArgumentIsALongOption()
    {
        $arguments = ['--debug'];

        $result = $this->handler->parse($arguments);

        $this->assertArrayHasKey('debug', $result['options']);
    }

    public function testHandleReturnsExpectedWhenArgumentIsACompoundOption()
    {
        $arguments = ['--decimal-prefix'];

        $result = $this->handler->parse($arguments);

        $this->assertArrayHasKey('prefix', $result['options']);
    }

    public function testHandleReturnsExpectedWhenArgumentsAreCorrectBenchmarksNames()
    {
        $arguments = ['-b', 'integers,floats'];

        $result = $this->handler->parse($arguments);

        $this->assertNotNull($this->dereference_key_recursive('integers', $result));
        $this->assertNotNull($this->dereference_key_recursive('floats', $result));
    }

    public function testHandleReturnsExpectedWhenArgumentsAreCorrectIteration()
    {
        $arguments = ['-i', 42];

        $result = $this->handler->parse($arguments);

        $this->assertEquals(42, $this->dereference_key_recursive('iterations', $result));
    }

    public function testHandleReturnsExpectedWhenArgumentsAreCorrectFilename()
    {
        $arguments = ['--temporary-file', 'test.txt'];

        $result = $this->handler->parse($arguments);

        $this->assertEquals('test.txt', $this->dereference_key_recursive('file', $result));
    }

    public function testHandleReturnsExpectedWhenSomeBenchmarksAreExcludedInStrictOrder()
    {
        $arguments = ['-a', '-e', 'integers,arrays'];

        $result = $this->handler->parse($arguments);

        $this->assertArrayNotHasKey('integers', $result['options']['benchmarks']);
        $this->assertNotNull($this->dereference_key_recursive('floats', $result));
        $this->assertArrayNotHasKey('arrays', $result['options']['benchmarks']);
    }

    public function testHandleReturnsExpectedWhenSomeBenchmarksAreExcludedNonStrictOrder()
    {
        $arguments = ['-e', 'integers,arrays', '-a'];

        $result = $this->handler->parse($arguments);

        $this->assertArrayNotHasKey('integers', $result['options']['benchmarks']);
        $this->assertNotNull($this->dereference_key_recursive('floats', $result));
        $this->assertArrayNotHasKey('arrays', $result['options']['benchmarks']);
    }

    /**
     * @dataProvider provideHandleActions
     * @param array $arguments
     * @param string $action
     * @throws EmptyArgumentException|WrongArgumentException|UnknownArgumentException
     */
    public function testHandleReturnsExpectedActions(array $arguments, $action)
    {
        $result = $this->handler->parse($arguments);

        $this->assertEquals($action, $result['action']);
    }

    public function provideHandleActions()
    {
        return [
            'help action short' => [['-h'], 'help'],
            'help action long' => [['--help'], 'help'],
            'all action short' => [['-a'], 'handle'],
            'all action long' => [['--all'], 'handle'],
            'exclude action short' => [['-e', 'integers', '-a'], 'handle'],
            'exclude action long' => [['--exclude', 'integers', '--all'], 'handle'],
            'benchmarks action short' => [['-b', 'integers'], 'handle'],
            'benchmarks action long' => [['--benchmarks', 'integers'], 'handle'],
            'list action short' => [['-l'], 'list'],
            'list action long' => [['--list'], 'list'],
            'version action long' => [['--version'], 'version'],
        ];
    }

    /**
     * Helpers.
     */

    /**
     * @param mixed $key
     * @param array $array
     * @return mixed
     */
    private function dereference_key_recursive($key, array $array)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($array),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $k => $v) {
            if ($k === $key) {
                return $v;
            }
        }

        return null;
    }
}
