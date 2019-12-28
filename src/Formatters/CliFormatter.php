<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Reporters;

class CliFormatter implements FormatterInterface
{
    const REPORT_WIDTH = 32;
    const REPORT_ROW = '-';
    const REPORT_COLUMN = '|';
    const REPORT_SPACE = ' ';
    const LIST_BULLET = ' - ';

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function showHeader($data, $style = '')
    {
        $result = '';

        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        $result .= $this->formatInput($data, 'center');
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;

        echo $result;
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function showFooter($data, $style = '')
    {
        $result = '';
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . PHP_EOL;
        $result .= $this->formatInput($data);

        echo $result;
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function showBlock($data, $style = '')
    {
        echo $this->formatInput($data, $style);
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
     * @param string $style
     * @return string
     */
    protected function formatInput($data, $style = '')
    {
        if (!is_string($data) && !is_array($data)) {
            return '' . PHP_EOL;
        }

        if (is_array($data)) {
            return $this->formatArray($data, $style);
        }

        return $this->formatString($data, $style);
    }

    /**
     * @param array $array
     * @param string $style
     * @return string
     */
    protected function formatArray(array $array, $style = '')
    {
        $result = '';

        if (key($array) === 0) {
            foreach ($array as $item) {
                $result .= $this->formatString($item, $style);
            }
        } else {
            foreach ($array as $name => $item) {
                $result .= $this->formatString($name . ': ' . $item, $style);
            }
        }

        return $result;
    }

    /**
     * @param string $string
     * @param string $style
     * @return string
     */
    protected function formatString($string, $style = '')
    {
        if (!is_string($string)) {
            return '' . PHP_EOL;
        }

        if (preg_match('/^(?:(?:e|exclude):)(.+)/Su', $string, $match)) {
            return $match[1] . PHP_EOL;
        }

        if ($style === 'center' || $style === 'centered') {
            $string = self::REPORT_COLUMN . $this->makeCentered($string) . self::REPORT_COLUMN;
        }

        if ($style === 'list') {
            $string = self::LIST_BULLET . $string;
        }

        return $string . PHP_EOL;
    }

    /**
     * @param string $input
     * @return string
     */
    protected function wrapCentered($input)
    {
        return self::REPORT_COLUMN . $this->makeCentered($input) . self::REPORT_COLUMN;
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
