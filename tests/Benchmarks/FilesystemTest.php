<?php

namespace BenchmarkPHP\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Benchmarks\Filesystem;
use BenchmarkPHP\Tests\TestHelpersTrait;

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
        $method = $this->getPrivateMethod($partialMock, 'initFile');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('file already exists');
        $method->invokeArgs($partialMock, [$options]);
    }

    public function testCalculateSpeedThrowsExceptionWhenTimeIs0()
    {
        $partialMock = $this->getPartialMockWithSkippedConstructor();
        $method = $this->getPrivateMethod($partialMock, 'calculateSpeed');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cannot be zero');
        $method->invokeArgs($partialMock, [42, 0]);
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

        $method = $this->getPrivateMethod($this->bench, 'initFile');
        $result = $method->invokeArgs($this->bench, [['file' => $file]]);

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

    public function testCalculateSpeedReturnsExpectedWhenDefaultPrecision()
    {
        $method = $this->getPrivateMethod($this->bench, 'calculateSpeed');
        $result = $method->invokeArgs($this->bench, [1048576, 1]);

        $this->assertEquals('1.048MB/s', $result);
    }

    /**
     * @dataProvider provideGenerateSizeForHumansBaseBinary
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGenerateSizeForHumansReturnsExpectedWhenBaseBinary($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'binary']);
        $method = $this->getPrivateMethod($this->bench, 'generateSizeForHumans');
        $result = $method->invokeArgs($bench, $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansBaseBinary()
    {
        return [
            'When size is in bytes' => [[512], '512'],
            'When size is less than kilobyte' => [[1000], '1000'],
            'When size is one kilobyte and default precise' => [[1024], '1.00K'],
            'When size is one kilobyte and precise is 3' => [[1024, 3], '1.000K'],
            'When size is in kilobytes and precise is 3' => [[1042, 3], '1.017K'],
            'When size is one megabyte and default precise' => [[1048576], '1.00M'],
            'When size is one megabyte and precise is 3' => [[1048576, 3], '1.000M'],
            'When size is in megabytes and precise is 3' => [[1049600, 3], '1.001M'],
            'When size is one gigabyte and default precise' => [[1073741824], '1.00G'],
            'When size is one gigabyte and precise is 3' => [[1073741824, 3], '1.000G'],
            'When size is less than gigabyte and precise is 3' => [[1049756976, 3, 3], '0.977G'],
            'When size is more than gigabyte and precise is 3' => [[1074790400, 3, 3], '1.001G'],
            'When size is one gigabyte and measure is in kilobytes' => [[1073741824, 2, 1], '1000000.00K'],
            'When size is one gigabyte and measure is in megabytes' => [[1073741824, 2, 2], '1000.00M'],
            'When size is one gigabyte and measure is in gigabytes' => [[1073741824, 2, 3], '1.00G'],
            'When size is one gigabyte and measure is in terabytes' => [[1073741824, 3, 4], '0.001T'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizeForHumansBaseDecimal
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGenerateSizeForHumansReturnsExpectedWhenBaseDecimal($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal']);
        $method = $this->getPrivateMethod($this->bench, 'generateSizeForHumans');
        $result = $method->invokeArgs($bench, $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumansBaseDecimal()
    {
        return [
            'When size is in bytes' => [[512], '512'],
            'When size is less than kilobyte' => [[1000], '1.00KB'],
            'When size is one kilobyte and default precise' => [[1024], '1.02KB'],
            'When size is one kilobyte and precise is 3' => [[1024, 3], '1.024KB'],
            'When size is in kilobytes and precise is 3' => [[1042, 3], '1.042KB'],
            'When size is one megabyte and default precise' => [[1048576], '1.04MB'],
            'When size is one megabyte and precise is 3' => [[1048576, 3], '1.048MB'],
            'When size is in megabytes and precise is 3' => [[1049600, 3], '1.049MB'],
            'When size is one gigabyte and default precise' => [[1073741824], '1.07GB'],
            'When size is one gigabyte and precise is 3' => [[1073741824, 3], '1.073GB'],
            'When size is in gigabytes and precise is 3' => [[1049756976, 3, 3], '1.049GB'],
            'When size is one gigabyte and measure is in kilobytes' => [[1073741824, 2, 1], '1073741.82KB'],
            'When size is one gigabyte and measure is in megabytes' => [[1073741824, 2, 2], '1073.74MB'],
            'When size is one gigabyte and measure is in gigabytes' => [[1073741824, 2, 3], '1.07GB'],
            'When size is one gigabyte and measure is in terabytes' => [[1073741824, 3, 4], '0.001TB'],
        ];
    }

    /**
     * @dataProvider provideGenerateSizePrefixBaseBinary
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGenerateSizePrefixReturnsExpectedWhenBaseBinary($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'binary']);
        $method = $this->getPrivateMethod($this->bench, 'generateSizePrefix');
        $result = $method->invokeArgs($bench, $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizePrefixBaseBinary()
    {
        return [
            'When out of range base' => [[-1], ''],
            'When 0 base' => [[0], ''],
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
     * @throws \ReflectionException
     */
    public function testGenerateSizePrefixReturnsExpectedWhenBaseDecimal($arguments, $expected)
    {
        $bench = new Filesystem(['prefix' => 'decimal']);
        $method = $this->getPrivateMethod($this->bench, 'generateSizePrefix');
        $result = $method->invokeArgs($bench, $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizePrefixBaseDecimal()
    {
        return [
            'When out of range base' => [[-1], ''],
            'When 0 base' => [[0], ''],
            'When 1 base' => [[1], 'KB'],
            'When 2 base' => [[2], 'MB'],
            'When 3 base' => [[3], 'GB'],
            'When 4 base' => [[4], 'TB'],
        ];
    }

    public function testFormatSizeReturnsExpectedWhenString()
    {
        $method = $this->getPrivateMethod($this->bench, 'formatSize');
        $result = $method->invokeArgs($this->bench, ['test']);

        $this->assertEquals('test', $result);
    }

    public function testFormatSizeReturnsExpectedWhenInt()
    {
        $method = $this->getPrivateMethod($this->bench, 'formatSize');
        $result = $method->invokeArgs($this->bench, [42]);

        $this->assertEquals(42, $result);
    }

    public function testFormatSizeReturnsExpectedWhenFloatAndPrecisionIs2()
    {
        $method = $this->getPrivateMethod($this->bench, 'formatSize');
        $result = $method->invokeArgs($this->bench, [2.729513, 2]);

        $this->assertEquals('2.73', $result);
    }

    public function testFormatSizeReturnsExpectedWhenFloatAndPrecisionIs3()
    {
        $method = $this->getPrivateMethod($this->bench, 'formatSize');
        $result = $method->invokeArgs($this->bench, [2.729513, 3]);

        $this->assertEquals('2.729', $result);
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
