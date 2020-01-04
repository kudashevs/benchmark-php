<?php
/*
 * This file is part of Benchmark PHP.
 *
 * (c) Sergey Kudashev <kudashevs@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BenchmarkPHP\Tests\Unit\Informers;

use PHPUnit\Framework\TestCase;
use BenchmarkPHP\Informers\Informer;
use BenchmarkPHP\Informers\InformerInterface;

class InformerTest extends TestCase
{
    /**
     * @var Informer Class must implement InformerInterface
     */
    protected $informer;

    protected function setUp()
    {
        $this->informer = new Informer();
    }

    /**
     * Mandatory tests.
     */
    public function testInstanceImplementsCertainInterface()
    {
        $this->assertInstanceOf(InformerInterface::class, $this->informer);
    }

    /**
     * Exceptions.
     */

    /**
     * Corner cases.
     */

    /**
     * Functionality.
     */
    public function testGetSystemInformationReturnsExpected()
    {
        $information = $this->informer->getSystemInformation();

        $this->assertInternalType('array', $information);
    }

    public function testGetSystemInformationContainsHost()
    {
        $host = gethostname();

        $information = $this->informer->getSystemInformation();
        $this->assertContains($host, $information['Server']);
    }

    public function testGetSystemInformationContainsPHPVersion()
    {
        $version = PHP_VERSION;

        $information = $this->informer->getSystemInformation();
        $this->assertContains($version, $information);
    }

    public function testGetSystemInformationContainsPlatform()
    {
        $os = PHP_OS;
        $platform = php_uname('m');

        $information = $this->informer->getSystemInformation();
        $this->assertContains($os, $information['Platform']);
        $this->assertContains($platform, $information['Platform']);
    }

    public function testGetSystemInformationXDebugWhenAvailable()
    {
        $version = phpversion('xdebug');
        $information = $this->informer->getSystemInformation();

        if ($this->isXDebugAvailable()) {
            $this->assertEquals($version, $information['Xdebug version']);
        } else {
            $this->assertEquals('not installed', $information['Xdebug']);
        }
    }

    private function isXDebugAvailable()
    {
        return in_array('xdebug', get_loaded_extensions());
    }
}
