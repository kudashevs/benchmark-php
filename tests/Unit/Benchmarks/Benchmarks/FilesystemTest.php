<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Benchmarks\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Tests\TestHelpersTrait;
use BenchmarkPHP\Benchmarks\Benchmarks\Filesystem;
use BenchmarkPHP\Exceptions\WrongArgumentException;
use BenchmarkPHP\Exceptions\BenchmarkRuntimeException;

class FilesystemTest extends TestCase
{
    use TestHelpersTrait;

    /** @var Filesystem */
    private $bench;

    protected function setUp()
    {
        $this->bench = new Filesystem(['testing' => true]);
    }

    /**
     * Exceptions.
     */
    public function testInitFileThrowsExceptionWhenFileExists()
    {
        $options = ['file' => __DIR__ . DIRECTORY_SEPARATOR . 'bench.txt'];

        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $this->expectException(BenchmarkRuntimeException::class);
        $this->expectExceptionMessage('file already exists');
        $this->runPrivateMethod($partialMock, 'initFile', [$options]);
    }

    public function testCalculateSpeedThrowsExceptionWhenTimeIs0()
    {
        $partialMock = $this->getPartialMockWithSkippedConstructor();

        $this->expectException(BenchmarkRuntimeException::class);
        $this->expectExceptionMessage('cannot be zero');
        $this->runPrivateMethod($partialMock, 'calculateSpeed', [42, 0]);
    }

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testInitFileReturnsExpectedWhenValidFileName()
    {
        $file = 'valid.txt';

        $result = $this->runPrivateMethod($this->bench, 'initFile', [['file' => $file]]);

        $this->assertEquals($file, $result);
    }

    public function testBeforeCreatesData()
    {
        $this->bench->before();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertEquals(Filesystem::FILE_SIZE, strlen($data));
    }

    public function testAfterRemovesData()
    {
        $this->bench->before();
        $this->bench->after();

        $data = $this->getPrivateVariableValue($this->bench, 'data');

        $this->assertEmpty($data);
    }

    public function testResultReturnsExpected()
    {
        $this->bench->before();
        $this->bench->handle();
        $this->bench->after();

        $result = $this->bench->result();

        $this->assertNotContains('Not handled yet', $result);
        $this->assertEquals(count($result), count(array_filter($result)));
    }

    public function testCalculateSpeedReturnsExpectedWhenDefaultPrecisionAndDefaultRounding()
    {
        $result = $this->runPrivateMethod($this->bench, 'calculateSpeed', [1048576, 1]);

        $this->assertEquals('1.049MB/s', $result);
    }

    public function testCalculateSpeedReturnsExpectedWhenPrecisionIs2AndWithRounding()
    {
        $bench = new Filesystem(['prefix' => 'decimal', 'data_precise' => 2, 'rounding' => true]);

        $result = $this->runPrivateMethod($bench, 'calculateSpeed', [1048576, 1]);

        $this->assertEquals('1.05MB/s', $result);
    }

    public function testCalculateSpeedReturnsExpectedWhenPrecisionIs2AndWithoutRounding()
    {
        $bench = new Filesystem(['prefix' => 'decimal', 'data_precise' => 2, 'rounding' => false]);

        $result = $this->runPrivateMethod($bench, 'calculateSpeed', [1048576, 1]);

        $this->assertEquals('1.04MB/s', $result);
    }

    /**
     * @dataProvider provideGenerateSizeForHumansWithRounding
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizeForHumansReturnExpectedWithRounding($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal', 'rounding' => true]);

        $result = $this->runPrivateMethod($bench, 'generateSizeForHumans', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansWithRounding()
    {
        return [
            'When size is 1 in bytes and default precision' => [[1, 3], '0.001KB'],
            'When size is 16 in bytes and precision 2' => [[16, 2], '0.02KB'],
            'When size is 32 in bytes and precision 2' => [[32, 2], '0.03KB'],
            'When size is 34 in bytes and precision 2' => [[34, 2], '0.03KB'],
            'When size is 35 in bytes and precision 2' => [[35, 2], '0.04KB'],
            'When size is 39 in bytes and precision 2' => [[39, 2], '0.04KB'],
            'When size is 49 in bytes and precision 1' => [[49, 1], '0.0KB'],
            'When size is 50 in bytes and precision 1' => [[50, 1], '0.1KB'],
            'When size is 51 in bytes and precision 1' => [[51, 1], '0.1KB'],
            'When size is 440 in bytes and precision 2' => [[440, 2], '0.44KB'],
            'When size is 450 in bytes and precision 2' => [[450, 2], '0.45KB'],
            'When size is 460 in bytes and precision 2' => [[460, 2], '0.46KB'],
            'When size is 440 in bytes and precision 1' => [[440, 1], '0.4KB'],
            'When size is 450 in bytes and precision 1' => [[450, 1], '0.5KB'],
            'When size is 460 in bytes and precision 1' => [[460, 1], '0.5KB'],
            'When size is 460 in bytes and precision 0' => [[460, 0], '0KB'],
            'When size is 990 in bytes and precision 2' => [[990, 2], '0.99KB'],
            'When size is 994 in bytes and precision 2' => [[994, 2], '0.99KB'],
            'When size is 995 in bytes and precision 2' => [[995, 2], '1.00KB'],
            'When size is 999 in bytes and precision 2' => [[999, 2], '1.00KB'],
            'When size is 1000 in bytes and precision 2' => [[1000, 2], '1.00KB'],
            'When size is 990 in bytes and precision 1' => [[990, 1], '1.0KB'],
            'When size is 994 in bytes and precision 1' => [[994, 1], '1.0KB'],
            'When size is 995 in bytes and precision 1' => [[995, 1], '1.0KB'],
            'When size is 999 in bytes and precision 1' => [[999, 1], '1.0KB'],
            'When size is 1000 in bytes and precision 1' => [[1000, 1], '1.0KB'],
            'When size is 1000 in bytes and precision 0' => [[1000, 0], '1KB'],
            'When size is 440 in kilobytes and precision 2' => [[440000, 2], '0.44MB'],
            'When size is 450 in kilobytes and precision 2' => [[450000, 2], '0.45MB'],
            'When size is 460 in kilobytes and precision 2' => [[460000, 2], '0.46MB'],
            'When size is 440 in kilobytes and precision 1' => [[440000, 1], '0.4MB'],
            'When size is 450 in kilobytes and precision 1' => [[450000, 1], '0.5MB'],
            'When size is 460 in kilobytes and precision 1' => [[460000, 1], '0.5MB'],
            'When size is 460 in kilobytes and precision 0' => [[460000, 0], '0MB'],
            'When size is 990 in kilobytes and precision 2' => [[990000, 2], '0.99MB'],
            'When size is 994 in kilobytes and precision 2' => [[994000, 2], '0.99MB'],
            'When size is 995 in kilobytes and precision 2' => [[995000, 2], '1.00MB'],
            'When size is 999 in kilobytes and precision 2' => [[999000, 2], '1.00MB'],
            'When size is 1000 in kilobytes and precision 2' => [[1000000, 2], '1.00MB'],
            'When size is 990 in kilobytes and precision 1' => [[990000, 1], '1.0MB'],
            'When size is 994 in kilobytes and precision 1' => [[994000, 1], '1.0MB'],
            'When size is 995 in kilobytes and precision 1' => [[995000, 1], '1.0MB'],
            'When size is 999 in kilobytes and precision 1' => [[999000, 1], '1.0MB'],
            'When size is 1000 in kilobytes and precision 1' => [[1000000, 1], '1.0MB'],
            'When size is 1000 in kilobytes and precision 0' => [[1000000, 0], '1MB'],
            'When size is 1 in bytes and out of precision' => [[1, 5], '0.001KB'],
            'When size is 1000 in kilobytes and out of precision' => [[1000000, 5], '1.000MB'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizeForHumansWithoutRounding
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizeForHumansReturnExpectedWithoutRounding($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal', 'rounding' => false]);

        $result = $this->runPrivateMethod($bench, 'generateSizeForHumans', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansWithoutRounding()
    {
        return [
            'When size is 1 in bytes and default precision' => [[1, 3], '0.001KB'],
            'When size is 16 in bytes and precision 2' => [[16, 2], '0.01KB'],
            'When size is 32 in bytes and precision 2' => [[32, 2], '0.03KB'],
            'When size is 34 in bytes and precision 2' => [[34, 2], '0.03KB'],
            'When size is 35 in bytes and precision 2' => [[35, 2], '0.03KB'],
            'When size is 39 in bytes and precision 2' => [[39, 2], '0.03KB'],
            'When size is 49 in bytes and precision 1' => [[49, 1], '0.0KB'],
            'When size is 50 in bytes and precision 1' => [[50, 1], '0.0KB'],
            'When size is 51 in bytes and precision 1' => [[51, 1], '0.0KB'],
            'When size is 440 in bytes and precision 2' => [[440, 2], '0.44KB'],
            'When size is 450 in bytes and precision 2' => [[450, 2], '0.45KB'],
            'When size is 460 in bytes and precision 2' => [[460, 2], '0.46KB'],
            'When size is 440 in bytes and precision 1' => [[440, 1], '0.4KB'],
            'When size is 450 in bytes and precision 1' => [[450, 1], '0.4KB'],
            'When size is 460 in bytes and precision 1' => [[460, 1], '0.4KB'],
            'When size is 460 in bytes and precision 0' => [[460, 0], '0KB'],
            'When size is 990 in bytes and precision 2' => [[990, 2], '0.99KB'],
            'When size is 994 in bytes and precision 2' => [[994, 2], '0.99KB'],
            'When size is 995 in bytes and precision 2' => [[995, 2], '0.99KB'],
            'When size is 999 in bytes and precision 2' => [[999, 2], '0.99KB'],
            'When size is 1000 in bytes and precision 2' => [[1000, 2], '1.00KB'],
            'When size is 990 in bytes and precision 1' => [[990, 1], '0.9KB'],
            'When size is 994 in bytes and precision 1' => [[994, 1], '0.9KB'],
            'When size is 995 in bytes and precision 1' => [[995, 1], '0.9KB'],
            'When size is 999 in bytes and precision 1' => [[999, 1], '0.9KB'],
            'When size is 1000 in bytes and precision 1' => [[1000, 1], '1.0KB'],
            'When size is 1000 in bytes and precision 0' => [[1000, 0], '1KB'],
            'When size is 440 in kilobytes and precision 2' => [[440000, 2], '0.44MB'],
            'When size is 450 in kilobytes and precision 2' => [[450000, 2], '0.45MB'],
            'When size is 460 in kilobytes and precision 2' => [[460000, 2], '0.46MB'],
            'When size is 440 in kilobytes and precision 1' => [[440000, 1], '0.4MB'],
            'When size is 450 in kilobytes and precision 1' => [[450000, 1], '0.4MB'],
            'When size is 460 in kilobytes and precision 1' => [[460000, 1], '0.4MB'],
            'When size is 460 in kilobytes and precision 0' => [[460000, 0], '0MB'],
            'When size is 990 in kilobytes and precision 2' => [[990000, 2], '0.99MB'],
            'When size is 994 in kilobytes and precision 2' => [[994000, 2], '0.99MB'],
            'When size is 995 in kilobytes and precision 2' => [[995000, 2], '0.99MB'],
            'When size is 999 in kilobytes and precision 2' => [[999000, 2], '0.99MB'],
            'When size is 1000 in kilobytes and precision 2' => [[1000000, 2], '1.00MB'],
            'When size is 990 in kilobytes and precision 1' => [[990000, 1], '0.9MB'],
            'When size is 994 in kilobytes and precision 1' => [[994000, 1], '0.9MB'],
            'When size is 995 in kilobytes and precision 1' => [[995000, 1], '0.9MB'],
            'When size is 999 in kilobytes and precision 1' => [[999000, 1], '0.9MB'],
            'When size is 1000 in kilobytes and precision 1' => [[1000000, 1], '1.0MB'],
            'When size is 1000 in kilobytes and precision 0' => [[1000000, 0], '1MB'],
            'When size is 1 in bytes and out of precision' => [[1, 5], '0.001KB'],
            'When size is 1000 in kilobytes and out of precision' => [[1000000, 5], '1.000MB'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizeForHumansBase1024
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizeForHumansReturnsExpectedWhenBaseBinary($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'binary', 'rounding' => false]);

        $result = $this->runPrivateMethod($bench, 'generateSizeForHumans', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansBase1024()
    {
        return [
            'When size is 1 in kibibytes' => [[1], '0.001K'],
            'When size is 16 in kibibytes' => [[16], '0.015K'],
            'When size is 32 in kibibytes' => [[32], '0.031K'],
            'When size is 512 in kibibytes' => [[512], '0.500K'],
            'When size is 1000 in kibibytes' => [[1000], '0.976K'],
            'When size is 1024 in kibibytes' => [[1024], '1.000K'], // 1 kibibyte
            'When size is 1042 in kibibytes' => [[1042], '1.017K'],
            'When size is 1047552 in mebibytes' => [[1047552], '0.999M'],
            'When size is 1048576 in mebibytes' => [[1048576], '1.000M'], // 1 mebibyte
            'When size is 1049600 in mebibytes' => [[1049600], '1.001M'],
            'When size is 1.015 mebibytes' => [[1063936], '1.014M'],
            'When size is 2.0 mebibytes' => [[2097152], '2.000M'],
            'When size is 3.5 mebibytes' => [[3670016], '3.500M'],
            'When size is 1049756976 in gibibytes' => [[1049756976], '0.977G'],
            'When size is 1073741824 in gibibytes' => [[1073741824], '1.000G'], // 1 gibibyte
            'When size is 1074790400 in gibibytes' => [[1074790400], '1.001G'],
            'When size is 3.0 gibibytes' => [[3221225472], '3.000G'],
            'When size is 3.1 gibibytes' => [[3326083072], '3.097G'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizeForHumansBase1000
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizeForHumansReturnsExpectedWhenBaseDecimal($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal', 'rounding' => false]);

        $result = $this->runPrivateMethod($bench, 'generateSizeForHumans', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansBase1000()
    {
        return [
            'When size is 1 in kilobytes' => [[1], '0.001KB'],
            'When size is 16 in kilobytes' => [[16], '0.016KB'],
            'When size is 32 in kilobytes' => [[32], '0.032KB'],
            'When size is 512 in kilobytes' => [[512], '0.512KB'],
            'When size is 900 in kilobytes' => [[900], '0.900KB'],
            'When size is 1000 in kilobytes' => [[1000], '1.000KB'], // 1 kilobyte
            'When size is 1024 in kilobytes' => [[1024], '1.024KB'],
            'When size is 1042 in kilobytes' => [[1042], '1.042KB'],
            'When size is 999000 in megabytes' => [[999000], '0.999MB'],
            'When size is 1000000 in megabytes' => [[1000000], '1.000MB'], // 1 megabyte
            'When size is 1047552 in megabytes' => [[1047552], '1.047MB'],
            'When size is 1048576 in megabytes' => [[1048576], '1.048MB'], // 1 mebibyte
            'When size is 1049600 in megabytes' => [[1049600], '1.049MB'],
            'When size is 1.015 mebibytes in megabytes' => [[1063936], '1.063MB'],
            'When size is 2.0 mebibytes in megabytes' => [[2097152], '2.097MB'],
            'When size is 3.5 mebibytes in megabytes' => [[3670016], '3.670MB'],
            'When size is 999000000 in gigabytes' => [[999000000], '0.999GB'],
            'When size is 1000000000 in gigabytes' => [[1000000000], '1.000GB'], // 1 gigabyte
            'When size is 1049756976 in gigabytes' => [[1049756976], '1.049GB'],
            'When size is 1073741824 in gigabytes' => [[1073741824], '1.073GB'], // 1 gibibyte
            'When size is 1074790400 in gigabytes' => [[1074790400], '1.074GB'],
            'When size is 3.0 gibibytes in gigabytes' => [[3221225472], '3.221GB'],
            'When size is 3.1 gibibytes in gigabytes' => [[3326083072], '3.326GB'],
        ];
    }

    /**
     * @dataProvider provideFormatSizeVarious
     * @param array $arguments
     * @param mixed $expected
     * @throws \ReflectionException
     */
    public function testFormatSizeReturnsExpected($arguments, $expected)
    {
        $result = $this->runPrivateMethod($this->bench, 'formatSize', $arguments);

        $this->assertEquals($expected, $result);
    }

    public function provideFormatSizeVarious()
    {
        return [
            'When argument is string' => [['test'], 'test'],
            'When argument is integer' => [[42], 42],
            'When argument is float and precision is 0' => [[2.729413, 0], 2],
            'When argument is float and precision is 1' => [[2.729413, 1], 2.7],
            'When argument is float and precision is 2' => [[2.729413, 2], 2.73],
            'When argument is float and precision is 3' => [[2.729413, 3], 2.729],
            'When argument is float and precision is 10' => [[2.7684543132, 10], 2.7684543132],
        ];
    }

    /**
     * @dataProvider provideGenerateSizePrefixBaseBinary
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizePrefixReturnsExpectedWhenBaseBinary($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'binary']);

        $result = $this->runPrivateMethod($bench, 'generateSizePrefix', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizePrefixBaseBinary()
    {
        return [
            'When out of range base' => [[-1], ''],
            'When 0 base' => [[0], 'B'],
            'When 1 base' => [[1], 'K'],
            'When 2 base' => [[2], 'M'],
            'When 3 base' => [[3], 'G'],
            'When 4 base' => [[4], 'T'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizePrefixBaseDecimal
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException|WrongArgumentException
     */
    public function testGenerateSizePrefixReturnsExpectedWhenBaseDecimal($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal']);

        $result = $this->runPrivateMethod($bench, 'generateSizePrefix', $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizePrefixBaseDecimal()
    {
        return [
            'When out of range base' => [[-1], ''],
            'When 0 base' => [[0], 'B'],
            'When 1 base' => [[1], 'KB'],
            'When 2 base' => [[2], 'MB'],
            'When 3 base' => [[3], 'GB'],
            'When 4 base' => [[4], 'TB'],
        ];
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
        $result = $this->runPrivateMethod($this->bench, 'isValidPrecision', [4]);

        $this->assertFalse($result);
    }

    /**
     * Helpers.
     */

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartialMockWithSkippedConstructor()
    {
        return $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
