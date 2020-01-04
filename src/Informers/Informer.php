<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Informers;

class Informer implements InformerInterface
{
    /**
     * @return array
     */
    public function getSystemInformation()
    {
        $result = [
            'Server' => $this->getHost(),
            'PHP version' => $this->getPHPVersion(),
            'Zend version' => $this->getZendVersion(),
            'Platform' => $this->getPlatform(),
        ];

        return $result;
    }

    /**
     * @return string
     */
    private function getHost()
    {
        $hostName = (($host = gethostname()) !== false) ? $host : '?';
        $ipAddress = ($ip = gethostbyname($hostName)) ? $ip : '?';

        return $hostName . '@' . $ipAddress;
    }

    /**
     * @return string
     */
    private function getPlatform()
    {
        return PHP_OS . ' (' . php_uname('m') . ')';
    }

    /**
     * @return string
     */
    private function getPHPVersion()
    {
        return phpversion();
    }

    /**
     * @return string
     */
    private function getZendVersion()
    {
        return zend_version();
    }
}
