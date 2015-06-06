<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\HandlerInterface;
use Joomla\Session\StorageInterface;

/**
 * Base class providing a session store
 *
 * @since  __DEPLOY_VERSION__
 */
class NativeStorage implements StorageInterface
{
	/**
	 * Flag if the session is active
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $active = false;

	/**
	 * Session save handler
	 *
	 * @var    \SessionHandlerInterface|HandlerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $handler;

	/**
	 * Internal flag whether the session has been started
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $started = false;

	/**
	 * Constructor
	 *
	 * @param   \SessionHandlerInterface  $handler  Session save handler
	 * @param   array                     $options  Session options
	 *
	 * @since   1.0
	 */
	public function __construct(\SessionHandlerInterface $handler = null, array $options = array())
	{
		ini_set('session.use_cookies', 1);
		session_cache_limiter('none');
		session_register_shutdown();

		$this->setOptions($options);
		$this->setHandler($handler);
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear($namespace)
	{
		$_SESSION[$namespace] = array();
	}

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
	public function get($name, $default, $namespace)
	{
		if (!$this->started)
		{
			$this->start();
		}

		if (isset($_SESSION[$namespace][$name]))
		{
			return $_SESSION[$namespace][$name];
		}

		return $default;
	}

	/**
	 * Gets the save handler instance
	 *
	 * @return  \SessionHandlerInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Get the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return session_name();
	}

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
	public function has($name, $namespace)
	{
		if (!$this->started)
		{
			$this->start();
		}

		return isset($_SESSION[$namespace][$name]);
	}

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive()
	{
		return $this->active = \PHP_SESSION_ACTIVE === session_status();
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove($name, $namespace)
	{
		if (!$this->started)
		{
			$this->start();
		}

		$old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

		unset($_SESSION[$namespace][$name]);

		return $old;
	}

	/**
	 * Set data into the session store
	 *
	 * @param   string $name      Name of a variable.
	 * @param   mixed  $value     Value of a variable.
	 * @param   string $namespace Namespace to use, default to 'default'.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		if (!$this->started)
		{
			$this->start();
		}

		$old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

		$_SESSION[$namespace][$name] = $value;

		return $old;
	}

	/**
	 * Registers session save handler as a PHP session handler
	 *
	 * @param   \SessionHandlerInterface  $handler  The save handler to use
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHandler(\SessionHandlerInterface $handler = null)
	{
		$this->handler = $handler;
		session_set_save_handler($this->handler, false);

		return $this;
	}

	/**
	 * Sets session.* ini variables.
	 *
	 * For convenience we omit 'session.' from the beginning of the keys.
	 * Explicitly ignores other ini keys.
	 *
	 * @param   array  $options  Session ini directives array(key => value).
	 *
	 * @return  $this
	 *
	 * @note    Based on \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage::setOptions()
	 * @see     http://php.net/session.configuration
	 * @since   __DEPLOY_VERSION__
	 */
	public function setOptions(array $options)
	{
		$validOptions = array_flip(
			array(
				'cache_limiter', 'cookie_domain', 'cookie_httponly', 'cookie_lifetime', 'cookie_path', 'cookie_secure', 'entropy_file',
				'entropy_length', 'gc_divisor', 'gc_maxlifetime', 'gc_probability', 'hash_bits_per_character', 'hash_function', 'name',
				'referer_check', 'serialize_handler', 'use_cookies', 'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
				'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name', 'upload_progress.freq', 'upload_progress.min-freq',
				'url_rewriter.tags'
			)
		);

		foreach ($options as $key => $value)
		{
			if (isset($validOptions[$key]))
			{
				ini_set('session.' . $key, $value);
			}
		}

		return $this;
	}

	/**
	 * Start a session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function start()
	{
		if ($this->started)
		{
			return true;
		}

		if (session_status() === \PHP_SESSION_ACTIVE)
		{
			throw new \RuntimeException('Failed to start the session: already started by PHP.');
		}

		if (ini_get('session.use_cookies') && headers_sent($file, $line))
		{
			throw new \RuntimeException(
				sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line)
			);
		}

		if (!session_start())
		{
			throw new \RuntimeException('Failed to start the session');
		}

		$this->isActive();
		$this->started = true;
	}
}
