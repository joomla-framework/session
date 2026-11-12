<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests;

use Joomla\Session\Handler\ApcuHandler;
use Joomla\Session\Handler\DatabaseHandler;
use Joomla\Session\Handler\MemcachedHandler;
use Joomla\Session\Handler\RedisHandler;
use Joomla\Session\Session;
use Joomla\Session\SessionInterface;
use Joomla\Session\Storage\NativeStorage;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\StorageInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the public API declares native return types instead of relying on #[\ReturnTypeWillChange].
 */
class NativeTypesTest extends TestCase
{
    public static function classProvider(): array
    {
        return [
            'ApcuHandler'      => [ApcuHandler::class],
            'DatabaseHandler'  => [DatabaseHandler::class],
            'MemcachedHandler' => [MemcachedHandler::class],
            'RedisHandler'     => [RedisHandler::class],
            'Session'          => [Session::class],
            'SessionInterface' => [SessionInterface::class],
            'NativeStorage'    => [NativeStorage::class],
            'RuntimeStorage'   => [RuntimeStorage::class],
            'StorageInterface' => [StorageInterface::class],
        ];
    }

    #[DataProvider('classProvider')]
    public function testAllMethodsDeclareANativeReturnType(string $class)
    {
        $untyped = [];

        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods() as $method) {
            // Skip inherited methods and methods from other packages' traits
            if ($method->isConstructor() || $method->getDeclaringClass()->getName() !== $class || $method->getFileName() !== $reflection->getFileName()) {
                continue;
            }

            if (!$method->hasReturnType() || $method->getAttributes(\ReturnTypeWillChange::class) !== []) {
                $untyped[] = $method->getName();
            }
        }

        $this->assertSame([], $untyped);
    }
}
