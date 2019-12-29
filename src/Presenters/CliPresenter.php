<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Presenters;

use BenchmarkPHP\Output\OutputInterface;

class CliPresenter implements PresenterInterface
{
    /**
     * @var string
     */
    const NEW_LINE = PHP_EOL;

    /**
     * @var int
     */
    const REPORT_WIDTH = 32;

    /**
     * @var string
     */
    const REPORT_ROW = '-';

    /**
     * @var string
     */
    const REPORT_COLUMN = '|';

    /**
     * @var string
     */
    const REPORT_SPACE = ' ';

    /**
     * @var string
     */
    const LIST_BULLET = ' - ';

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $data
     * @return void
     */
    public function version($data)
    {
        $result = $data . self::NEW_LINE . self::NEW_LINE;

        $this->output->write($result);
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function header($data, $style = '')
    {
        $result = '';

        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . self::NEW_LINE;
        $result .= $this->formatInput($data, 'center');
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . self::NEW_LINE;

        $this->output->write($result);
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function footer($data, $style = '')
    {
        $result = '';
        $result .= str_repeat(self::REPORT_ROW, self::REPORT_WIDTH) . self::NEW_LINE;
        $result .= $this->formatInput($data);

        $this->output->write($result);
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return void
     */
    public function block($data, $style = '')
    {
        $this->output->write($this->formatInput($data, $style));
    }

    /**
     * @return void
     */
    public function separator()
    {
        $this->output->write(str_repeat(self::REPORT_ROW, self::REPORT_WIDTH));
    }

    /**
     * @return void
     */
    public function success()
    {
        $this->output->terminateOnSuccess();
    }

    /**
     * @param int $code
     * @return void
     */
    public function error($code)
    {
        $this->output->terminateOnError($code);
    }

    /**
     * @param string|array $data
     * @param string $style
     * @return string
     */
    protected function formatInput($data, $style = '')
    {
        if (!is_string($data) && !is_array($data)) {
            return '' . self::NEW_LINE;
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
            return '' . self::NEW_LINE;
        }

        if (preg_match('/^(?:(?:e|exclude):)(.+)/Su', $string, $match)) {
            return $match[1] . self::NEW_LINE;
        }

        if ($style === 'center' || $style === 'centered') {
            $string = self::REPORT_COLUMN . $this->makeCentered($string) . self::REPORT_COLUMN;
        }

        if ($style === 'list') {
            $string = self::LIST_BULLET . $string;
        }

        return $string . self::NEW_LINE;
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
