<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcacheHandler;
use Joomla\Test\TestDatabase;

/**
 * Test class for Joomla\Session\Handler\MemcacheHandler.
 */
class MemcacheHandlerTest extends TestDatabase
{
	/**
	 * MemcacheHandler for testing
	 *
	 * @var  MemcacheHandler
	 */
	private $handler;

	/**
	 * Mock Memcache object for testing
	 *
	 * @var  \PHPUnit_Framework_MockObject_MockObject
	 */
	private $memcache;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->memcache = $this->getMock('Memcache');
		$this->handler  = new MemcacheHandler($this->memcache);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertSame(
			(extension_loaded('memcache') && class_exists('Memcache')),
			MemcacheHandler::isSupported()
		);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->memcache->expects($this->once())
			->method('close')
			->willReturn(true);

		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->memcache->expects($this->once())
			->method('get')
			->with('id')
			->willReturn('foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->memcache->expects($this->once())
			->method('set')
			->with('id', 'data', 0)
			->willReturn(true);

		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->memcache->expects($this->once())
			->method('delete')
			->with('id')
			->willReturn(true);

		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
