<?php

namespace BenchmarkPHP\Reporters;

class CliReporter implements Reporter
{
    const REPORT_WIDTH = 32;
    const REPORT_ROW = '-';
    const REPORT_COLUMN = '|';
    const REPORT_SPACE = ' ';

    /**
     * @param array $data
     * @return string
     */
    public function showHeader(array $data)
    {
        $result = '';

        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        foreach ($data as $item) {
            $result .= self::REPORT_COLUMN . $this->makeCentered($item) . self::REPORT_COLUMN . PHP_EOL;
        }
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;

        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    public function showFooter(array $data)
    {
        $result = '';
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        foreach ($data as $name => $item) {
            $result .= $name . ': ' . $item . PHP_EOL;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    public function showBlock(array $data)
    {
        $result = '';

        foreach ($data as $name => $item) {
            $result .= $name . ': ' . $item . PHP_EOL;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function showSeparator()
    {
        return str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
    }

    /**
     * Make some text centered.
     *
     * @param string $item
     * @return string
     */
    protected function makeCentered($item)
    {
        $possibleWidth = self::REPORT_WIDTH - 2;

        if (!is_string($item)) {
            return str_repeat(self::REPORT_SPACE, $possibleWidth);
        }

        $length = mb_strlen($item);
        if ($length > $possibleWidth) {
            return substr($item, 0, $possibleWidth);
        }

        $half = ($possibleWidth - $length) / 2;

        if (is_float($half)) {
            return str_repeat(self::REPORT_SPACE, (int)floor($half)) . $item . str_repeat(self::REPORT_SPACE, (int)ceil($half));
        }

        return str_repeat(self::REPORT_SPACE, $half) . $item . str_repeat(self::REPORT_SPACE, $half);
    }
}
