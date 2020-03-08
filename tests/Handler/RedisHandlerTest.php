<?php
/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\RedisHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Handler\RedisHandler.
 */
class RedisHandlerTest extends TestCase
{
	/**
	 * RedisHandler for testing
	 *
	 * @var  RedisHandler
	 */
	private $handler;

	/**
	 * Mock Redis object for testing
	 *
	 * @var  \Redis
	 */
	private $redis;

	/**
	 * Options to inject into the handler
	 *
	 * @var  array
	 */
	private $options = ['prefix' => 'jfwtest_', 'ttl' => 1000];

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass(): void
	{
		// Make sure the handler is supported in this environment
		if (!RedisHandler::isSupported())
		{
			static::markTestSkipped('The RedisHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->redis = new \Redis();

		if (!$this->redis->connect('127.0.0.1', 6379))
		{
			unset($this->redis);
			$this->markTestSkipped('Cannot connect to Redis.');
		}

		$this->handler = new RedisHandler($this->redis, $this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertSame(
			(extension_loaded('redis') && class_exists('Redis')),
			RedisHandler::isSupported()
		);
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->handler->write('id', 'foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectlyWithATimeToLive()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectlyWithoutATimeToLive()
	{
		$handler = new RedisHandler($this->redis, ['prefix' => 'jfwtest_', 'ttl' => 0]);

		$this->assertTrue($handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
