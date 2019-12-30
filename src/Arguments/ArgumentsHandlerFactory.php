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

use BenchmarkPHP\Input\InputInterface;
use BenchmarkPHP\Exceptions\RuntimeException;

class ArgumentsHandlerFactory
{
    /**
     * @param InputInterface $input
     * @return ArgumentsHandlerInterface
     */
    public function create(InputInterface $input)
    {
        $type = $this->classBaseName($input);

        if ($type === 'CliInput') {
            return new CliArgumentsHandler();
        }

        throw new RuntimeException('Cannot create correct ArgumentsHandler instance. Check input.');
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
