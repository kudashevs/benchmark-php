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
        $result = [];

        $result['Server'] = $this->getHost();
        $result['Platform'] = $this->getPlatform();
        $result['PHP version'] = $this->getPHPVersion();
        $result['Zend version'] = $this->getZendVersion();

        if ($this->isXDebugInstalled()) {
            $result['Xdebug version'] = $this->getXDebugVersion();
        } else {
            $result['Xdebug'] = 'not installed';
        }

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

    /**
     * @return bool
     */
    private function isXDebugInstalled()
    {
        return in_array('xdebug', get_loaded_extensions(), true);
    }

    /**
     * @return string
     */
    private function getXDebugVersion()
    {
        return phpversion('xdebug');
    }
}
