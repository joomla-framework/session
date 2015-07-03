<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcachedHandler;
use Joomla\Test\TestDatabase;

/**
 * Test class for Joomla\Session\Handler\MemcachedHandler.
 */
class MemcachedHandlerTest extends TestDatabase
{
	/**
	 * MemcachedHandler for testing
	 *
	 * @var  MemcachedHandler
	 */
	private $handler;

	/**
	 * Mock Memcached object for testing
	 *
	 * @var  \PHPUnit_Framework_MockObject_MockObject
	 */
	private $memcached;

	/**
	 * Options to inject into the handler
	 *
	 * @var  array
	 */
	private $options = array('prefix' => 'jfwtest_', 'ttl' => 1000);

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		if (version_compare(phpversion('memcached'), '2.2.0', '>='))
		{
			$this->markTestSkipped('Tests can only be run with memcached extension 2.1.0 or lower');
		}

		$this->memcached = $this->getMock('Memcached');
		$this->handler  = new MemcachedHandler($this->memcached, $this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertSame(
			(class_exists('Memcached')),
			MemcachedHandler::isSupported()
		);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->memcached->expects($this->once())
			->method('get')
			->with($this->options['prefix'] . 'id')
			->willReturn('foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->memcached->expects($this->once())
			->method('set')
			->with($this->options['prefix'] . 'id', 'data', $this->equalTo(time() + $this->options['ttl'], 2))
			->willReturn(true);

		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->memcached->expects($this->once())
			->method('delete')
			->with($this->options['prefix'] . 'id')
			->willReturn(true);

		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
