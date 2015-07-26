<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcacheHandler;

/**
 * Test class for Joomla\Session\Handler\MemcacheHandler.
 */
class MemcacheHandlerTest extends \PHPUnit_Framework_TestCase
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

		$this->memcache = $this->getMock('Memcache');
		$this->handler  = new MemcacheHandler($this->memcache, $this->options);
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
			->with($this->options['prefix'] . 'id')
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
			->with($this->options['prefix'] . 'id', 'data', 0, $this->equalTo(time() + $this->options['ttl'], 2))
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
			->with($this->options['prefix'] . 'id')
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
