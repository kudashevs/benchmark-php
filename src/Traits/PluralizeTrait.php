<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Traits;

trait PluralizeTrait
{
    /**
     * @param int $count
     * @param string $text
     * @return string
     */
    private function generatePluralized($count, $text)
    {
        $text = trim($text);

        return ($count === 1 || substr($text, -1) === 's') ? $count . ' ' . $text : $count . ' ' . $text . 's'; // todo add -es for -s ending words
    }
}
