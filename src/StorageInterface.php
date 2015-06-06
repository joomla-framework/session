<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

/**
 * Interface defining a Joomla! session storage object
 *
 * @since  __DEPLOY_VERSION__
 */
interface StorageInterface
{
	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName();

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName($name);

	/**
	 * Get the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getId();

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setId($id);

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive();

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStarted();

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name       Name of a variable
	 * @param   mixed   $default    Default value of a variable if not set
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($name, $default, $namespace);

	/**
	 * Set data into the session store
	 *
	 * @param   string  $name       Name of a variable
	 * @param   mixed   $value      Value of a variable
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set($name, $value, $namespace);

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function has($name, $namespace);

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove($name, $namespace);

	/**
	 * Clears all variables from the session store
	 *
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear($namespace);

	/**
	 * Retrieves all variables from the session store
	 *
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all($namespace);

	/**
	 * Start a session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function start();

	/**
	 * Writes session data and ends session
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   __DEPLOY_VERSION__
	 */
	public function close();
}
