<?php

namespace BenchmarkPHP\Reporters;

class CliReporter implements Reporter
{
    const REPORT_WIDTH = 32;
    const REPORT_ROW = '-';
    const REPORT_COLUMN = '|';
    const REPORT_SPACE = ' ';

    /**
     * @param string|array $data
     * @return void
     */
    public function showHeader($data)
    {
        $result = '';

        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        $result .= $this->formatInput($data, true);
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;

        echo $result;
    }

    /**
     * @param string|array $data
     * @return void
     */
    public function showFooter($data)
    {
        $result = '';
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        $result .= $this->formatInput($data);

        echo $result;
    }

    /**
     * @param string|array $data
     * @return void
     */
    public function showBlock($data)
    {
        echo $this->formatInput($data);
    }

    /**
     * @return void
     */
    public function showSeparator()
    {
        echo str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
    }

    /**
     * @param string|array $data
     * @param bool $centered
     * @return string
     */
    protected function formatInput($data, $centered = false)
    {
        if (!is_string($data) && !is_array($data)) {
            return '' . PHP_EOL;
        }

        if (is_string($data)) {
            return $this->wrapCentered($data, $centered);
        }

        $result = '';

        if (key($data) === 0) {
            foreach ($data as $item) {
                $result .= $this->wrapCentered($item, $centered);
            }
        } else {
            foreach ($data as $name => $item) {
                $result .= $this->wrapCentered($name . ': ' . $item, $centered);
            }
        }

        return $result;
    }

    /**
     * @param string $input
     * @param bool $centered
     * @return string
     */
    protected function wrapCentered($input, $centered = false)
    {
        return (!$centered)
            ? $input . PHP_EOL
            : self::REPORT_COLUMN . $this->makeCentered($input) . self::REPORT_COLUMN . PHP_EOL;
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
