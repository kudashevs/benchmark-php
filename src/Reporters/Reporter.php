<?php

namespace BenchmarkPHP\Reporters;

interface Reporter
{
    /**
     * @param string|array $data
     * @return string
     */
    public function showHeader($data);

    /**
     * @param string|array $data
     * @return string
     */
    public function showFooter($data);

    /**
     * @param string|array $data
     * @return string
     */
    public function showBlock($data);

    /**
     * @return string
     */
    public function showSeparator();
}
