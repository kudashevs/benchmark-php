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

interface Reporter
{
    /**
     * @param string|array $data
     * @param string $style
     * @return string|void
     */
    public function showHeader($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return string|void
     */
    public function showFooter($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return string|void
     */
    public function showBlock($data, $style = '');

    /**
     * @return string|void
     */
    public function showSeparator();
}
