<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Sqlite\SqliteDriver;
use Joomla\Session\Handler\DatabaseHandler;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\Operation\Composite;
use PHPUnit\DbUnit\Operation\Factory;
use PHPUnit\DbUnit\Operation\Operation;
use PHPUnit\DbUnit\TestCase;

/**
 * Test class for Joomla\Session\Handler\DatabaseHandler.
 */
class DatabaseHandlerTest extends TestCase
{
	/**
	 * The active database driver being used for the tests.
	 *
	 * @var  DatabaseDriver
	 */
	private static $driver;

	/**
	 * DatabaseHandler for testing
	 *
	 * @var  DatabaseHandler
	 */
	private $handler;

	/**
	 * The database driver options for the connection.
	 *
	 * @var  array
	 */
	private static $options = ['driver' => 'sqlite', 'database' => ':memory:'];

	/**
	 * Flag if the session table has been created
	 *
	 * @var  boolean
	 */
	private static $sessionTableCreated = false;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * An example DSN would be: host=localhost;port=5432;dbname=joomla_ut;user=utuser;pass=ut1234
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the driver is supported
		if (!SqliteDriver::isSupported())
		{
			static::markTestSkipped('The SQLite driver is not supported on this platform.');
		}

		try
		{
			// Attempt to instantiate the driver.
			static::$driver = DatabaseDriver::getInstance(static::$options);

			// Get the PDO instance for an SQLite memory database and load the test schema into it.
			static::$driver->connect();
		}
		catch (\RuntimeException $e)
		{
			static::$driver = null;
		}
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		if (static::$driver !== null)
		{
			static::$driver->disconnect();
			static::$driver = null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->handler = new DatabaseHandler(static::$driver);

		// Make sure our session table is present
		if (!self::$sessionTableCreated)
		{
			$this->handler->createDatabaseTable();

			self::$sessionTableCreated = true;
		}
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  DefaultConnection
	 */
	protected function getConnection()
	{
		if (static::$driver === null)
		{
			static::fail('Could not fetch a database driver to establish the connection.');
		}

		static::$driver->connect();

		return $this->createDefaultDBConnection(static::$driver->getConnection(), static::$options['database']);
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  ArrayDataSet
	 */
	protected function getDataSet()
	{
		return $this->createArrayDataSet([]);
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  Operation
	 */
	protected function getSetUpOperation()
	{
		// Required given the use of InnoDB contraints.
		return new Composite(
			[
				Factory::DELETE_ALL(),
				Factory::INSERT(),
			]
		);
	}

	/**
	 * Returns the database operation executed in test cleanup.
	 *
	 * @return  Operation
	 */
	protected function getTearDownOperation()
	{
		// Required given the use of InnoDB contraints.
		return Factory::DELETE_ALL();
	}

	/**
	 * @covers  Joomla\Session\Handler\DatabaseHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(DatabaseHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\DatabaseHandler::close()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::destroy()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::gc()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::read()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::open()
	 * @covers  Joomla\Session\Handler\DatabaseHandler::write()
	 */
	public function testValidateSessionDataIsCorrectlyReadWrittenAndDestroyed()
	{
		$sessionData = ['foo' => 'bar', 'joomla' => 'rocks'];
		$sessionId   = 'sid';

		$this->assertTrue($this->handler->open('', $sessionId));
		$this->assertTrue($this->handler->write($sessionId, json_encode(['foo' => 'bar'])));
		$this->assertTrue($this->handler->write($sessionId, json_encode($sessionData)));
		$this->assertSame($sessionData, json_decode($this->handler->read($sessionId), true));
		$this->assertTrue($this->handler->destroy($sessionId));
		$this->assertTrue($this->handler->gc(900));
		$this->assertTrue($this->handler->close());
	}
}
