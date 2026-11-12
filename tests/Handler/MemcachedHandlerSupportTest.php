<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcachedHandler;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Handler\MemcachedHandler::isSupported(), independent of a Memcached server.
 */
class MemcachedHandlerSupportTest extends TestCase
{
    public function testTheHandlerIsSupportedOnlyWithTheExtension()
    {
        $this->assertSame(\extension_loaded('memcached'), MemcachedHandler::isSupported());
    }

    #[RunInSeparateProcess]
    public function testAMemcachedClassWithoutTheExtensionIsNotEnough()
    {
        if (\extension_loaded('memcached')) {
            $this->markTestSkipped('The memcached extension is loaded.');
        }

        eval('class Memcached {}');

        $this->assertFalse(MemcachedHandler::isSupported());
    }
}
