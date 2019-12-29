<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Formatters;

interface FormatterInterface
{
    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function header($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function footer($data, $style = '');

    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function block($data, $style = '');

    /**
     * @return mixed
     */
    public function separator();

    public function success();

    public function error();
}
