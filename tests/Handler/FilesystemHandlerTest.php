<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\FilesystemHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Handler\FilesystemHandler.
 */
class FilesystemHandlerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        // Make sure the handler is supported in this environment
        if (!FilesystemHandler::isSupported()) {
            static::markTestSkipped('The FilesystemHandler is unsupported in this environment.');
        }
    }

    /**
     * @covers  Joomla\Session\Handler\FilesystemHandler
     */
    public function testTheHandlerIsSupported()
    {
        $this->assertTrue(FilesystemHandler::isSupported());
    }

    /**
     * @covers  Joomla\Session\Handler\FilesystemHandler
     */
    public function testTheHandlerHandlesAnInvalidPath()
    {
        $this->expectException(\InvalidArgumentException::class);

        new FilesystemHandler('totally;invalid;string;for;this;object');
    }

    /**
     * @covers  Joomla\Session\Handler\FilesystemHandler
     */
    public function testTheHandlerIsInstantiatedCorrectly()
    {
        $phpSessionPath = ini_get('session.save_path');

        if (empty($phpSessionPath)) {
            $this->expectException('\InvalidArgumentException');
        }

        $handler = new FilesystemHandler();

        $this->assertSame('files', ini_get('session.save_handler'));
    }

    /**
     * @covers  Joomla\Session\Handler\FilesystemHandler
     */
    public function testTheHandlerIsInstantiatedCorrectlyAndCreatesTheSavePathIfNeeded()
    {
        $handler = new FilesystemHandler(__DIR__ . '/savepath');

        // Temporarily skip this assertion due to changes in PHP 7.2
        // $this->assertSame(__DIR__ . '/savepath', ini_get('session.save_path'));
        $this->assertTrue(is_dir(realpath(__DIR__ . '/savepath')));

        rmdir(__DIR__ . '/savepath');
    }

    /**
     * @param   string  $savePath          The path to inject into the handler
     * @param   string  $expectedSavePath  The expected save path in the PHP configuration
     * @param   string  $path              The expected filesystem path for the handler
     *
     * @covers  Joomla\Session\Handler\FilesystemHandler
     */
    #[DataProvider('savePathDataProvider')]
    public function testTheHandlerIsInstantiatedCorrectlyAndHandlesAllParametersAsExpected($savePath, $expectedSavePath, $path)
    {
        $handler = new FilesystemHandler($savePath);

        // Temporarily skip this assertion due to changes in PHP 7.2
        // $this->assertEquals($expectedSavePath, ini_get('session.save_path'));
        $this->assertTrue(is_dir(realpath($path)));

        rmdir($path);
    }

    public function testTheHandlerCreatesANestedSavePath()
    {
        $base = sys_get_temp_dir() . '/jfw-session-nested';
        $path = $base . '/sub/dir';

        try {
            new FilesystemHandler($path);

            $this->assertDirectoryExists($path);
        } finally {
            @rmdir($path);
            @rmdir($base . '/sub');
            @rmdir($base);
        }
    }

    public function testTheHandlerCreatesTheSavePathOnlyAccessibleToTheOwner()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Unix file permissions are not available on Windows.');
        }

        $path = sys_get_temp_dir() . '/jfw-session-mode';

        try {
            new FilesystemHandler($path);

            clearstatcache();
            $this->assertSame('0700', substr(sprintf('%o', fileperms($path)), -4));
        } finally {
            @rmdir($path);
        }
    }

    public function testTheHandlerWarnsWhenTheSavePathCannotBeSet()
    {
        $savePath = ini_get('session.save_path');
        $path     = sys_get_temp_dir() . '/jfw-session-active';
        $errors   = [];

        ini_set('session.save_path', sys_get_temp_dir());
        session_start();

        set_error_handler(
            static function (int $level, string $message) use (&$errors): bool {
                $errors[] = [$level, $message];

                return true;
            }
        );

        try {
            new FilesystemHandler($path);
        } finally {
            restore_error_handler();
            session_abort();
            session_id('');
            ini_set('session.save_path', $savePath);
            @rmdir($path);
        }

        $this->assertContains([E_USER_WARNING, sprintf('The session save path could not be set to "%s".', $path)], $errors);
    }

    /**
     * Data provider with expected paths for handler construction
     *
     * @return  array
     */
    public static function savePathDataProvider(): array
    {
        $base = sys_get_temp_dir();

        return [
            ["$base/savepath", "$base/savepath", "$base/savepath"],
            ["5;$base/savepath", "5;$base/savepath", "$base/savepath"],
            ["5;0600;$base/savepath", "5;0600;$base/savepath", "$base/savepath"],
        ];
    }
}
