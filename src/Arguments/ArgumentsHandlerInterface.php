<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Arguments;

interface ArgumentsHandlerInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function validate(array $data);
}
