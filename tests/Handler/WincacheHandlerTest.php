<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\WincacheHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Handler\WincacheHandler.
 */
class WincacheHandlerTest extends TestCase
{
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!WincacheHandler::isSupported())
		{
			static::markTestSkipped('The WincacheHandler is unsupported in this environment.');
		}
	}

	/**
	 * @covers  Joomla\Session\Handler\WincacheHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(WincacheHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\WincacheHandler::__construct()
	 */
	public function testTheHandlerIsInstantiatedCorrectly()
	{
		$handler = new WincacheHandler;

		$expected = headers_sent() ? ini_get('session.save_handler') : 'wincache';

		$this->assertSame($expected, ini_get('session.save_handler'));
	}
}
