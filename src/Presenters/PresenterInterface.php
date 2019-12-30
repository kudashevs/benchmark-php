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
     * @return mixed
     */
    public function header($data);

    /**
     * @param string|array $data
     * @return mixed
     */
    public function footer($data);

    /**
     * @param string|array $data
     * @return mixed
     */
    public function block($data);

    /**
     * @param string|array $data
     * @return mixed
     */
    public function listing($data);

    /**
     * @return mixed
     */
    public function separator();

    /**
     * If we want to execute some code after successful execution.
     *
     * @return mixed
     */
    public function onSuccess();

    /**
     * If we want to execute some code after critical error.
     *
     * @param mixed $data
     * @return mixed
     */
    public function onError($data);
}
