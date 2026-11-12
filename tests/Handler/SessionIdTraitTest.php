<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\ApcuHandler;
use Joomla\Session\Handler\DatabaseHandler;
use Joomla\Session\Handler\MemcachedHandler;
use Joomla\Session\Handler\RedisHandler;
use Joomla\Session\Handler\SessionIdTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Handler\SessionIdTrait.
 */
class SessionIdTraitTest extends TestCase
{
    public static function handlerProvider(): array
    {
        return [
            'APCu'      => [ApcuHandler::class],
            'Database'  => [DatabaseHandler::class],
            'Memcached' => [MemcachedHandler::class],
            'Redis'     => [RedisHandler::class],
        ];
    }

    #[DataProvider('handlerProvider')]
    public function testTheHandlerImplementsTheSessionIdInterfaces(string $class)
    {
        $this->assertContains(\SessionIdInterface::class, class_implements($class));
        $this->assertContains(\SessionUpdateTimestampHandlerInterface::class, class_implements($class));
    }

    public static function sidSettingsProvider(): array
    {
        return [
            '32 characters, 4 bits' => [32, 4, '/^[0-9a-f]{32}$/'],
            '26 characters, 5 bits' => [26, 5, '/^[0-9a-v]{26}$/'],
            '48 characters, 6 bits' => [48, 6, '/^[0-9a-zA-Z,-]{48}$/'],
        ];
    }

    #[DataProvider('sidSettingsProvider')]
    public function testANewIdHonoursTheConfiguredLengthAndCharacters(int $length, int $bits, string $pattern)
    {
        if (headers_sent()) {
            $this->markTestSkipped('Session ini settings cannot be changed after output has been sent.');
        }

        $previousLength = ini_get('session.sid_length');
        $previousBits   = ini_get('session.sid_bits_per_character');

        // The settings are deprecated as of PHP 8.4, but still in effect
        $this->silently(
            static function () use ($length, $bits) {
                ini_set('session.sid_length', (string) $length);
                ini_set('session.sid_bits_per_character', (string) $bits);
            }
        );

        try {
            $handler = new SessionIdTraitStub();
            $id      = $handler->create_sid();

            $this->assertMatchesRegularExpression($pattern, $id);
            $this->assertNotSame($id, $handler->create_sid());
        } finally {
            $this->silently(
                static function () use ($previousLength, $previousBits) {
                    ini_set('session.sid_length', $previousLength);
                    ini_set('session.sid_bits_per_character', $previousBits);
                }
            );
        }
    }

    public function testUpdatingTheTimestampWritesTheData()
    {
        $handler = new SessionIdTraitStub();

        $this->assertTrue($handler->updateTimestamp('id', 'data'));
        $this->assertSame(['id' => 'data'], $handler->written);
    }

    /**
     * Runs a callback with all errors suppressed
     *
     * @param   callable  $callback  The callback
     *
     * @return  void
     */
    private function silently(callable $callback): void
    {
        set_error_handler(static fn (): bool => true);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }
    }
}

/**
 * Minimal handler using the trait
 */
class SessionIdTraitStub
{
    use SessionIdTrait;

    public array $written = [];

    public function write($id, $data)
    {
        $this->written[$id] = $data;

        return true;
    }
}
