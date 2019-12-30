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
use BenchmarkPHP\Exceptions\RuntimeException;

class PresenterFactory
{
    /**
     * @param OutputInterface $input
     * @return PresenterInterface
     */
    public function create(OutputInterface $input)
    {
        $type = $this->classBaseName($input);

        if ($type === 'CliOutput') {
            return new CliPresenter($input);
        }

        throw new RuntimeException('Cannot create correct Presenter instance. Check input.');
    }

    /**
     * @param object $object
     * @return string
     */
    private function classBaseName($object)
    {
        $split = explode('\\', get_class($object));

        return array_pop($split);
    }
}
