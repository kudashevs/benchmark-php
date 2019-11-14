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

    public function testCalculateSpeedReturnsExpected()
    {
        $method = $this->getPrivateMethod($this->bench, 'calculateSpeed');
        $result = $method->invokeArgs($this->bench, [1048576, 1]);

        $this->assertEquals('1.00M/s', $result);
    }

    /**
     * @dataProvider provideGenerateSizeForHumans
     * @param array $arguments
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGenerateSizeForHumansReturnsExpected($arguments, $expected)
    {
        $method = $this->getPrivateMethod($this->bench, 'generateSizeForHumans');
        $result = $method->invokeArgs($this->bench, $arguments);

        $this->assertSame($expected, $result);
    }

    public function provideGenerateSizeForHumans()
    {
        return [
            'When size is in bytes' => [[512], '512'],
            'When size is less than kilobyte' => [[1000], '1000'],
            'When size is one kilobyte and default precise' => [[1024], '1.00K'],
            'When size is one kilobyte and precise is 3' => [[1024, 3], '1.000K'],
            'When size is in kilobytes and precise is 3' => [[1042, 3], '1.018K'],
            'When size is one megabyte and default precise' => [[1048576], '1.00M'],
            'When size is one megabyte and precise is 3' => [[1048576, 3], '1.000M'],
            'When size is in megabytes and precise is 3' => [[1049600, 3], '1.001M'],
            'When size is one gigabyte and default precise' => [[1073741824], '1.00G'],
            'When size is one gigabyte and precise is 3' => [[1073741824, 3], '1.000G'],
            'When size is in gigabytes and precise is 3' => [[1049756976, 3, 3], '0.978G'],
            'When size is one gigabyte and measure is in kilobytes' => [[1073741824, 2, 1], '1000000.00K'],
            'When size is one gigabyte and measure is in megabytes' => [[1073741824, 2, 2], '1000.00M'],
            'When size is one gigabyte and measure is in gigabytes' => [[1073741824, 2, 3], '1.00G'],
            'When size is one gigabyte and measure is in terabytes' => [[1073741824, 3, 4], '0.001T'],
        ];
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
