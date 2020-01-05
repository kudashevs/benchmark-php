<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Benchmarks\Benchmarks;

use BenchmarkPHP\Exceptions\WrongArgumentException;
use BenchmarkPHP\Exceptions\BenchmarkRuntimeException;

class Filesystem extends AbstractBenchmark
{
    /**
     * @var int We use binary kilobyte as base.
     */
    const BASE_SIZE = 1024;

    /**
     * @var int Multiplier means 1 = Kb, 2 = Mb, 3 = Gb.
     */
    const BASE_MULTIPLIER = 2;

    /**
     * @var int Number of measuring units.
     */
    const BASE_COUNT = 8;

    /**
     * @var int
     */
    const FILE_SIZE = self::BASE_COUNT * (self::BASE_SIZE ** self::BASE_MULTIPLIER);

    /**
     * @var int Processed data output precision (possible values are from 0 to 3).
     */
    const DATA_PRECISION = 3;

    /**
     * @var bool
     */
    const DATA_ROUNDING = true;

    /**
     * @var int
     */
    protected $iterations = 100;

    /**
     * @var string
     */
    private $file;

    /**
     * @var resource
     */
    private $handler;

    /**
     * @var int
     */
    private $base;

    /**
     * @param array $options
     * @throws WrongArgumentException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->file = $this->initFile($options);
        $this->handler = $this->initHandler();
        $this->base = $this->initBase($options);
    }

    public function __destruct()
    {
        fclose($this->handler);
        unlink($this->file);
    }

    /**
     * @param array $options
     * @return string
     */
    protected function initFile(array $options)
    {
        if (isset($options['file'])) {
            if (file_exists($options['file'])) {
                throw new BenchmarkRuntimeException('Unable to create ' . $options['file'] . ' because file already exists.');
            }

            $file = $options['file'];
        } else {
            $file = tempnam(sys_get_temp_dir(), 'bench');
        }

        return $file;
    }

    /**
     * @return resource
     */
    protected function initHandler()
    {
        $handler = fopen($this->file, 'w+b');

        if (!is_resource($handler)) {
            throw new BenchmarkRuntimeException('Unable to open temporary file handler.');
        }

        return $handler;
    }

    /**
     * Returns measurement base. Default base is 1000 (decimal prefix).
     *
     * @param array $options
     * @return int
     */
    private function initBase(array $options)
    {
        if (isset($options['prefix']) && $options['prefix'] === 'binary') {
            return 1024;
        }

        return 1000;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $diffTime = 0;
        $writeTime = 0;
        $readTime = 0;

        for ($i = 0; $i < $this->iterations; $i++) {
            $benchDataLength = strlen($this->data);
            $this->resetFilePointer();

            $startTime = microtime(true);

            fwrite($this->handler, $this->data, $benchDataLength);

            $stopTime = microtime(true);
            $writeTime += $stopTime - $startTime;
            $diffTime += $stopTime - $startTime;

            $this->checkWriteOperation($benchDataLength);

            $benchFileLength = filesize($this->file);

            $this->resetFilePointer();

            $startTime = microtime(true);

            $data = fread($this->handler, $benchFileLength);

            $stopTime = microtime(true);
            $readTime += $stopTime - $startTime;
            $diffTime += $stopTime - $startTime;

            $this->checkReadOperation(strlen($data));
        }

        $this->statistics = [
            'write_time' => $writeTime,
            'read_time' => $readTime,
            'exec_time' => $diffTime,
        ];
    }

    /**
     * @return void
     */
    public function before()
    {
        $this->data = $this->generateTestData();
    }

    /**
     * @return void
     */
    public function after()
    {
        $this->data = null;
    }

    /**
     * @return array
     */
    public function result()
    {
        $initKeys = ['exec_time'];
        $result = array_intersect_key($this->statistics, array_flip($initKeys));

        $result = array_merge($this->getOperationsSummary(), $result);

        return $result;
    }

    /**
     * @return string
     */
    protected function generateTestData()
    {
        return str_repeat('a', self::FILE_SIZE);
    }

    /**
     * @return void
     */
    private function resetFilePointer()
    {
        fseek($this->handler, 0);
    }

    /**
     * @param int $length
     * @return void
     */
    private function checkWriteOperation($length)
    {
        if (filesize($this->file) !== $length) {
            throw new BenchmarkRuntimeException('The amount of data written doesn\'t match the file size.');
        }
    }

    /**
     * @param int $length
     * @return void
     */
    private function checkReadOperation($length)
    {
        if ($length !== self::FILE_SIZE) {
            throw new BenchmarkRuntimeException('The amount of data read doesn\'t match the file size.');
        }
    }

    /**
     * @return array
     */
    private function getOperationsSummary()
    {
        $size = self::FILE_SIZE * $this->iterations;

        $summary = [
            'read_time' => $this->statistics['read_time'],
            'read_speed' => $this->calculateSpeed($size, $this->statistics['read_time']),
            'write_time' => $this->statistics['write_time'],
            'write_speed' => $this->calculateSpeed($size, $this->statistics['write_time']),
        ];

        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $summary = [
                'iterate' => $this->generatePluralizedCount($this->iterations, 'time'),
                'read_time' => $this->statistics['read_time'],
                'read_speed' => $this->calculateSpeed($size, $this->statistics['read_time']),
                'write_time' => $this->statistics['write_time'],
                'write_speed' => $this->calculateSpeed($size, $this->statistics['write_time']),
            ];
        }

        return $summary;
    }

    /**
     * @param int $size
     * @param int|float $time
     * @param int $precision
     * @return string
     */
    private function calculateSpeed($size, $time, $precision = self::DATA_PRECISION)
    {
        if ($time == 0) {
            throw new BenchmarkRuntimeException('The ' . __FUNCTION__ . ' time argument cannot be zero. Check argument value.');
        }

        return $this->generateSizeForHumans($size / $time, $precision) . '/s';
    }

    /**
     * @param int|float $size Size in bytes.
     * @param int $precision
     * @return string
     */
    private function generateSizeForHumans($size, $precision = null)
    {
        $precision = $this->isValidPrecision($precision) ? $precision : self::DATA_PRECISION;

        if (isset($this->options['data_precise']) && $this->isValidPrecision($this->options['data_precise'])) {
            $precision = $this->options['data_precise'];
        }

        $ration = log($size, $this->base);
        $measure = (int)round($ration);

        if ($size < $this->base) {
            $measure = 1;
        }

        $calculated = $this->base ** ($ration - $measure);

        return $this->formatSize($calculated, $precision) . $this->generateSizePrefix($measure);
    }

    /**
     * @param mixed $precision
     * @return bool
     */
    private function isValidPrecision($precision)
    {
        if (!is_int($precision)) {
            return false;
        }

        /**
         * We don't want precision more than 3 because with thousandths it is meaningless.
         */
        return $precision >= 0 && $precision <= 3;
    }

    /**
     * @param int|float $size
     * @param int $precision
     * @return string
     */
    private function formatSize($size, $precision = self::DATA_PRECISION)
    {
        if (!is_numeric($size)) {
            return $size;
        }

        if (is_int($size) || $precision === 0) {
            return (string)floor($size);
        }

        $rounding = self::DATA_ROUNDING;

        if (isset($this->options['rounding']) && is_bool($this->options['rounding'])) {
            $rounding = $this->options['rounding'];
        }

        if ($rounding) {
            return number_format($size, $precision, '.', '');
        }

        /*
         * We don't want to round the last digit as number_format does, so we increase
         * precision by number and then we will cut the last digits in the output.
         * We check precision for 1 because we don't want to rounding 0.9 to 1.0.
         */
        $cut = ($precision <= 1) ? 2 : 1;
        $unrounded = number_format($size, $precision + $cut, '.', '');

        return substr($unrounded, 0, -$cut);
    }

    /**
     * @param int $measure
     * @return string
     */
    private function generateSizePrefix($measure)
    {
        if ($this->base === 1000) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        } else {
            $units = ['B', 'K', 'M', 'G', 'T'];
        }

        if ($measure < 0 || $measure > count($units)) {
            return '';
        }

        return $units[$measure];
    }
}
