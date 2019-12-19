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
        $host = gethostname();
        $version = PHP_VERSION;
        $os = PHP_OS;
        $platform = php_uname('m');

        $information = $this->informer->getSystemInformation();
        $this->assertContains($host, $information['Server']);
        $this->assertContains($version, $information);
        $this->assertContains($os, $information['Platform']);
        $this->assertContains($platform, $information['Platform']);
    }
}
