<?php

namespace BenchmarkPHP\Benchmarks;

class Filesystem extends AbstractBenchmark
{
    /**
     * @var int We start with kilobyte.
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
     * @var int Default base is the binary prefix.
     */
    private $base;

    /**
     * Create a new FileSystem instance.
     *
     * @param array $options
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
                throw new \InvalidArgumentException('Unable to create ' . $options['file'] . ' because file already exists.');
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
            throw new \InvalidArgumentException('Unable to open temporary file handler.');
        }

        return $handler;
    }

    /**
     * @param array $options
     * @return int
     */
    protected function initBase(array $options)
    {
        if (array_key_exists('prefix', $options) && $options['prefix'] === 'decimal') {
            return 1000;
        }

        return 1024;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $initTime = microtime(true);
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
            'start_time' => $initTime,
            'stop_time' => $initTime - $diffTime,
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
    protected function resetFilePointer()
    {
        fseek($this->handler, 0);
    }

    /**
     * @param int $length
     * @return void
     */
    protected function checkWriteOperation($length)
    {
        if (filesize($this->file) !== $length) {
            throw new \RuntimeException('The amount of data written doesn\'t match the file size.');
        }
    }

    /**
     * @param int $length
     * @return void
     */
    protected function checkReadOperation($length)
    {
        if ($length !== self::FILE_SIZE) {
            throw new \RuntimeException('The amount of data read doesn\'t match the file size.');
        }
    }

    /**
     * @return array
     */
    protected function getOperationsSummary()
    {
        $size = self::FILE_SIZE * $this->iterations;

        $summary = [
            'read_speed' => $this->calculateSpeed($size, $this->statistics['read_time']),
            'write_speed' => $this->calculateSpeed($size, $this->statistics['write_time']),
        ];

        if ($this->isVerboseMode() || $this->isDebugMode()) {
            $summary = [
                'iterate' => $this->generatePluralizedCount($this->iterations, 'time'),
                'read_speed' => $this->calculateSpeed($size, $this->statistics['read_time']),
                'read_time' => $this->statistics['read_time'],
                'write_speed' => $this->calculateSpeed($size, $this->statistics['write_time']),
                'write_time' => $this->statistics['write_time'],
            ];
        }

        return $summary;
    }

    /**
     * @param int $size
     * @param float $time
     * @param int $precision
     * @return string
     */
    protected function calculateSpeed($size, $time, $precision = 2)
    {
        return $this->generateSizeForHumans($size / $time, $precision) . '/s';
    }

    /**
     * @param int|float $size Size in bytes.
     * @param int $precision
     * @param int $measure
     * @return string
     */
    protected function generateSizeForHumans($size, $precision = 2, $measure = null)
    {
        if ($size < $this->base) {
            return $this->formatSize($size, 0);
        }

        $base = log($size, $this->base);
        $measure = ($measure !== null) ? $measure : (int)floor($base);

        $calculated = round(1000 ** ($base - $measure), 4);

        return $this->formatSize($calculated, $precision) . $this->generateSizePrefix($measure);
    }

    /**
     * @param int|float $size
     * @param int $precision
     * @return string
     */
    protected function formatSize($size, $precision = 2)
    {
        if (!is_numeric($size)) {
            return $size;
        }

        if (is_int($size)) {
            return (string)$size;
        }

        /*
        * we don't want to round the last digit as number_format does, so we increase
        * precision by one and then we will cut the last digit in the output
        */
        $formatted = number_format($size, $precision + 1, '.', '');

        return substr($formatted, 0, -1);
    }

    /**
     * @param int $measure
     * @return string
     */
    protected function generateSizePrefix($measure)
    {
        if ($this->base === 1000) {
            $units = ['', 'KB', 'MB', 'GB', 'TB'];
        } else {
            $units = ['', 'K', 'M', 'G', 'T'];
        }

        if ($measure < 0 || $measure > count($units)) {
            return '';
        }

        return $units[$measure];
    }
}
