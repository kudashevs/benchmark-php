<?php

namespace BenchmarkPHP\Reporters;

interface Reporter
{
    /**
     * @param string|array $data
     * @param string $style
     * @return string
     */
    public function showHeader($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return string
     */
    public function showFooter($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return string
     */
    public function showBlock($data, $style = '');

    /**
     * @return string
     */
    public function showSeparator();
}
