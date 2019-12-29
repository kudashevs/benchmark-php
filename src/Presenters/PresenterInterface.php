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

interface PresenterInterface
{
    /**
     * @param string $data
     * @return mixed
     */
    public function version($data);

    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function header($data, $style = ''); // todo remove $style

    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function footer($data, $style = ''); // todo remove $style

    /**
     * @param string|array $data
     * @param string $style
     * @return mixed
     */
    public function block($data, $style = ''); // todo remove $style

    /**
     * @return mixed
     */
    public function separator();

    /**
     * @return mixed
     */
    public function success();

    /**
     * @param mixed $data
     * @return mixed
     */
    public function error($data);
}
