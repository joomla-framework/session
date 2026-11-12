<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Storage\NativeStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Storage\NativeStorage.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class NativeStorageTest extends TestCase
{
    /**
     * Save path for our storage handler
     *
     * @var  string
     */
    private $savePath;

    /**
     * Storage object for testing
     *
     * @var  NativeStorage
     */
    private $storage;

    /**
     * Save the current set value
     *
     * @var   string
     */
    private $iniSaveHandler;

    /**
     * Save the current set value
     *
     * @var   string
     */
    private $iniSavePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->savePath = sys_get_temp_dir() . '/jfw-test';

        $this->iniSaveHandler = ini_get('session.save_handler');
        $this->iniSavePath = ini_get('session.save_path');

        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', $this->savePath);

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0755);
        }

        $this->storage = new NativeStorage(new FilesystemHandler($this->savePath));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        // Reset our session data
        session_write_close();
        array_map('unlink', glob($this->savePath . '/*'));

        if (is_dir($this->savePath)) {
            rmdir($this->savePath);
        }

        ini_set('session.save_handler', $this->iniSaveHandler);
        ini_set('session.save_path', $this->iniSavePath);

    }

    /**
     * Data provider for set tests
     *
     * @return  array
     */
    public static function setProvider(): array
    {
        return [
            ['joomla', 'rocks'],
            ['joomla.framework', 'too much awesomeness'],
        ];
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheStorageIsStartedCorrectly()
    {
        // There shouldn't be an ID yet
        $this->assertEmpty($this->storage->getId());

        // The storage should successfully start
        $this->storage->start();
        $this->assertTrue($this->storage->isStarted());

        // There should now be an ID
        $this->assertNotEmpty($this->storage->getId());

        // And the storage should be active
        $this->assertTrue($this->storage->isActive());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateAnInjectedSessionIdIsUsedWhenTheSessionStarts()
    {
        $mockId = '1234abcd';

        // Inject our ID
        $this->storage->setId($mockId);

        // The storage should successfully start
        $this->storage->start();

        // The ID should match our injected value
        $this->assertSame($mockId, $this->storage->getId());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheSessionIdCannotBeChangedAfterTheSessionIsStarted()
    {
        $this->expectException(\LogicException::class);

        // The storage should successfully start
        $this->storage->start();

        // Setting an ID now should throw an exception
        $this->storage->setId('fail');
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateAnInjectedSessionNameIsUsedWhenTheSessionStarts()
    {
        $mockName = '1234abcd';

        // Inject our name
        $this->storage->setName($mockName);

        // The storage should successfully start
        $this->storage->start();

        // The name should match our injected value
        $this->assertSame($mockName, $this->storage->getName());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheSessionNameCannotBeChangedAfterTheSessionIsStarted()
    {
        $this->expectException(\LogicException::class);

        // The storage should successfully start
        $this->storage->start();

        // Setting an name now should throw an exception
        $this->storage->setName('fail');
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheCorrectValueIsReturnedWhenGetIsCalled()
    {
        // Default return null
        $this->assertNull($this->storage->get('foo', null));

        // Return the specified default
        $this->assertSame('bar', $this->storage->get('foo', 'bar'));
    }

    /**
     * @param   string  $key    The key to set
     * @param   string  $value  The value to set
     *
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    #[DataProvider('setProvider')]
    public function testValidateAValueIsCorrectlyStored($key, $value)
    {
        $this->storage->set($key, $value);
        $this->assertSame($value, $this->storage->get($key, null));
    }

    /**
     * @param   string  $key    The key to set
     * @param   string  $value  The value to set
     *
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    #[DataProvider('setProvider')]
    public function testValidateTheKeyIsCorrectlyCheckedForExistence($key, $value)
    {
        $this->storage->set($key, $value);
        $this->assertTrue($this->storage->has($key));
        $this->assertFalse($this->storage->has($key . '.fake.ending'));
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateAKeyIsCorrectlyRemovedFromTheStore()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->has('foo'));

        $this->storage->remove('foo');
        $this->assertFalse($this->storage->has('foo'));
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateAllDataIsReturnedFromTheStore()
    {
        // Set some data into our session
        $this->storage->set('foo', 'bar');
        $this->storage->set('joomla.framework', 'is awesome');

        $this->assertSame(
            [
                'foo'              => 'bar',
                'joomla.framework' => 'is awesome',
            ],
            $this->storage->all()
        );

        // Now clear the data
        $this->storage->clear();
        $this->assertEmpty($this->storage->all());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheStorageIsCorrectlyRegenerated()
    {
        // First we need to start the store
        $this->storage->start();

        // Grab the session ID to check in a moment
        $sessionId = $this->storage->getId();

        // And add some data to validate it is carried forward
        $this->storage->set('foo', 'bar');

        // Now regenerate the store
        $this->assertTrue($this->storage->regenerate());

        // Validate the regeneration
        $this->assertNotSame($sessionId, $this->storage->getId());
        $this->assertArrayHasKey('foo', $this->storage->all());
        $this->assertTrue($this->storage->isActive());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage
     */
    public function testValidateTheStorageIsCorrectlyClosed()
    {
        $this->storage->start();
        $this->storage->close();

        // Validate the closure
        $this->assertFalse($this->storage->isStarted());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage::getHandler()
     */
    public function testValidateTheHandlerIsCorrectlyRetrieved()
    {
        $this->assertInstanceOf(FilesystemHandler::class, $this->storage->getHandler());
    }

    /**
     * @covers  Joomla\Session\Storage\NativeStorage::setHandler()
     * @uses    Joomla\Session\Storage\NativeStorage::getHandler()
     */
    public function testValidateTheHandlerIsCorrectlyInjected()
    {
        $handler = new FilesystemHandler($this->savePath);
        $this->storage->setHandler($handler);

        $this->assertSame($handler, $this->storage->getHandler());
    }

    public function testValidateTheSameSiteCookieOptionIsApplied()
    {
        $previous = ini_get('session.cookie_samesite');

        try {
            $this->storage->setOptions(['cookie_samesite' => 'Lax']);

            $this->assertSame('Lax', ini_get('session.cookie_samesite'));
        } finally {
            ini_set('session.cookie_samesite', $previous);
        }
    }

    public function testValidateAnUnknownOptionTriggersANotice()
    {
        $errors = $this->captureErrors(fn () => $this->storage->setOptions(['cookie_samesitee' => 'Lax', 'url_rewriter.tags' => 'a=href']));

        $this->assertContains(
            [E_USER_NOTICE, 'The session option "cookie_samesitee" is not supported and was ignored.'],
            $errors
        );
        $this->assertContains(
            [E_USER_NOTICE, 'The session option "url_rewriter.tags" is not supported and was ignored.'],
            $errors
        );
    }

    public function testValidateOptionsForAnActiveSessionTriggerANotice()
    {
        $this->storage->start();

        $errors = $this->captureErrors(fn () => $this->storage->setOptions(['gc_maxlifetime' => 60]));

        $this->assertSame(
            [[E_USER_NOTICE, 'The session options "gc_maxlifetime" were not applied because the session is already active.']],
            $errors
        );
    }

    public static function deprecatedOptionProvider(): array
    {
        return [
            'sid_length'             => ['sid_length', 32, true],
            'sid_bits_per_character' => ['sid_bits_per_character', 5, true],
            'trans_sid_tags'         => ['trans_sid_tags', 'a=href', true],
            'trans_sid_hosts'        => ['trans_sid_hosts', 'example.com', true],
            'referer_check set'      => ['referer_check', 'example.com', true],
            'referer_check empty'    => ['referer_check', '', false],
            'use_only_cookies off'   => ['use_only_cookies', 0, true],
            'use_only_cookies on'    => ['use_only_cookies', 1, false],
            'use_trans_sid on'       => ['use_trans_sid', 1, true],
            'use_trans_sid off'      => ['use_trans_sid', 0, false],
        ];
    }

    #[DataProvider('deprecatedOptionProvider')]
    public function testValidateOptionsDeprecatedInPhp84TriggerADeprecation(string $key, $value, bool $deprecated)
    {
        $previous = ini_get('session.' . $key);

        try {
            $errors = $this->captureErrors(fn () => $this->storage->setOptions([$key => $value]));
        } finally {
            ini_set('session.' . $key, $previous);
        }

        $packageDeprecations = array_filter(
            $errors,
            static fn (array $error) => $error[0] === E_USER_DEPRECATED && str_contains($error[1], '"' . $key . '"')
        );

        $this->assertCount($deprecated ? 1 : 0, $packageDeprecations);
    }

    /**
     * Runs a callback and returns the errors it triggered as [level, message] pairs
     *
     * @param   callable  $callback  The callback to run
     *
     * @return  array
     */
    private function captureErrors(callable $callback): array
    {
        $errors = [];

        set_error_handler(
            static function (int $level, string $message) use (&$errors): bool {
                $errors[] = [$level, $message];

                return true;
            }
        );

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $errors;
    }
}
