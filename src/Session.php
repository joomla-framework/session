<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Storage\NativeStorage;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides more advanced features such as expire timeouts.
 *
 * @since       1.0
 * @deprecated  The joomla/session package is deprecated
 */
class Session implements SessionInterface
{
	/**
	 * Internal state.
	 * One of 'inactive'|'active'|'expired'|'destroyed'|'closed'|'error'
	 *
	 * @var    string
	 * @see    getState()
	 * @since  1.0
	 */
	protected $state = 'inactive';

	/**
	 * Session namespace prefix
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $prefix = '__';

	/**
	 * Maximum age of unused session in minutes
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $expire = 15;

	/**
	 * The session store object.
	 *
	 * @var    StorageInterface
	 * @since  1.0
	 */
	protected $store;

	/**
	 * Security policy.
	 * List of checks that will be done.
	 *
	 * Possible values:
	 * - fix_browser
	 * - fix_address
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $security = array('fix_browser');

	/**
	 * Force cookies to be SSL only
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $force_ssl = false;

	/**
	 * The domain to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	protected $cookie_domain;

	/**
	 * The path to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	protected $cookie_path;

	/**
	 * Holds the Input object
	 *
	 * @var    Input
	 * @since  1.0
	 */
	private $input = null;

	/**
	 * Holds the Dispatcher object
	 *
	 * @var    DispatcherInterface
	 * @since  1.0
	 */
	private $dispatcher = null;

	/**
	 * Constructor
	 *
	 * @param   StorageInterface  $store    A StorageInterface implementation
	 * @param   array             $options  Optional parameters
	 *
	 * @since   1.0
	 */
	public function __construct(StorageInterface $store = null, array $options = array())
	{
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id())
		{
			session_unset();
			session_destroy();
		}

		// Disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		// Only allow the session ID to come from cookies and nothing else.
		ini_set('session.use_only_cookies', '1');

		// Create handler
		if (!($store instanceof StorageInterface))
		{
			$store = new NativeStorage(new FilesystemHandler);
		}

		$this->store = $store;

		// Set options
		$this->setOptions($options);

		$this->setCookieParams();

		$this->state = 'inactive';
	}

	/**
	 * Get expiration time in minutes
	 *
	 * @return  integer  The session expiration time in minutes
	 *
	 * @since   1.0
	 */
	public function getExpire()
	{
		return $this->expire;
	}

	/**
	 * Get current state of session
	 *
	 * @return  string  The session state
	 *
	 * @since   1.0
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Retrieve an external iterator.
	 *
	 * @return  \ArrayIterator  Return an ArrayIterator of $_SESSION.
	 *
	 * @since   1.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->all());
	}

	/**
	 * Get session name
	 *
	 * @return  string  The session name
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		return $this->store->getName();
	}

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName($name)
	{
		$this->store->setName($name);

		return $this;
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session name
	 *
	 * @since   1.0
	 */
	public function getId()
	{
		return $this->store->getId();
	}

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setId($id)
	{
		$this->store->setId($id);

		return $this;
	}

	/**
	 * Get the available session handlers
	 *
	 * @return  array  An array of available session handlers
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getHandlers()
	{
		$connectors = array();

		// Get an iterator and loop trough the handler classes.
		$iterator = new \DirectoryIterator(__DIR__ . '/Handler');

		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for PHP files.
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace('.php', '', '\\Joomla\\Session\\Storage\\' . ucfirst(trim($fileName)));

			// If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
			if (!class_exists($class))
			{
				continue;
			}

			// Sweet!  Our class exists, so now we just need to know if it passes its test method.
			if ($class::isSupported())
			{
				// Connector names should not have file the handler suffix or the file extension.
				$connectors[] = str_ireplace('Handler.php', '', $fileName);
			}
		}

		return $connectors;
	}

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function isActive()
	{
		if ($this->getState() === 'active')
		{
			return $this->store->isActive();
		}

		return false;
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function isNew()
	{
		$counter = $this->get('session.counter');

		return (bool) ($counter === 1);
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStarted()
	{
		return $this->store->isStarted();
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @param   Input       $input       Input object for the session to use.
	 * @param   Dispatcher  $dispatcher  Dispatcher object for the session to use.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function initialise(Input $input = null, DispatcherInterface $dispatcher = null)
	{
		// If injected as options in the constructor, don't overwrite with nulls
		if ($input instanceof Input)
		{
			$this->input = $input;
		}

		if ($dispatcher instanceof DispatcherInterface)
		{
			$this->dispatcher = $dispatcher;
		}
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name       Name of a variable
	 * @param   mixed   $default    Default value of a variable if not set
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   1.0
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = $this->prefix . $namespace;

		return $this->store->get($name, $default, $namespace);
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string  $name       Name of a variable.
	 * @param   mixed   $value      Value of a variable.
	 * @param   string  $namespace  Namespace to use, default to 'default'.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   1.0
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = $this->prefix . $namespace;

		return $this->store->set($name, $value, $namespace);
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   1.0
	 */
	public function has($name, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions.
		$namespace = $this->prefix . $namespace;

		return $this->store->has($name, $namespace);
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove($name, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = $this->prefix . $namespace;

		return $this->store->remove($name, $namespace);
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear($namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = $this->prefix . $namespace;

		$this->store->clear($namespace);
	}

	/**
	 * Retrieves all variables from the session store
	 *
	 * @param   string  $namespace  Namespace to use
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all($namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = $this->prefix . $namespace;

		return $this->store->all($namespace);
	}

	/**
	 * Start a session.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function start()
	{
		if ($this->isStarted())
		{
			return;
		}

		$this->store->start();

		$this->state = 'active';

		// Initialise the session
		$this->setCounter();
		$this->setTimers();

		// Perform security checks
		$this->validate();

		if ($this->dispatcher instanceof DispatcherInterface)
		{
			$this->dispatcher->triggerEvent('onAfterSessionStart');
		}
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the $_SESSION variable and destroys all of the data associated
	 * with the current session in its storage (file or DB). It forces new session to be
	 * started after this method is called.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   1.0
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->getState() === 'destroyed')
		{
			return true;
		}

		$this->clear();
		$this->fork(true);

		$this->state = 'destroyed';

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     destroy
	 * @since   1.0
	 */
	public function restart()
	{
		// Backup existing session data
		$data = $this->all();

		$this->destroy();

		if ($this->getState() !== 'destroyed')
		{
			// @TODO :: generated error here
			return false;
		}

		// Restart the session
		$this->store->start();

		$this->setCounter();
		$this->setTimers();
		$this->validate(true);

		// Restore the data
		foreach ($data as $key => $value)
		{
			$this->set($key, $value);
		}

		if ($this->dispatcher instanceof DispatcherInterface)
		{
			$this->dispatcher->triggerEvent('onAfterSessionRestart');
		}

		return true;
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @param   boolean  $destroy  Whether to delete the old session or leave it to garbage collection.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	public function fork($destroy = false)
	{
		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return false;
		}

		$this->store->regenerate($destroy);

		if ($destroy)
		{
			$this->setCounter();
			$this->setTimers();
		}

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call {@link Session::close()}, but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   1.0
	 */
	public function close()
	{
		$this->store->close();
		$this->state = 'closed';
	}

	/**
	 * Set session cookie parameters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setCookieParams()
	{
		$cookie = session_get_cookie_params();

		if ($this->force_ssl)
		{
			$cookie['secure'] = true;
		}

		if ($this->cookie_domain)
		{
			$cookie['domain'] = $this->cookie_domain;
		}

		if ($this->cookie_path)
		{
			$cookie['path'] = $this->cookie_path;
		}

		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setCounter()
	{
		$counter = $this->get('session.counter', 0);
		++$counter;

		$this->set('session.counter', $counter);

		return true;
	}

	/**
	 * Set the session timers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setTimers()
	{
		if (!$this->has('session.timer.start'))
		{
			$start = time();

			$this->set('session.timer.start', $start);
			$this->set('session.timer.last', $start);
			$this->set('session.timer.now', $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return true;
	}

	/**
	 * Set additional session options
	 *
	 * @param   array  $options  List of parameter
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setOptions(array $options)
	{
		// Set name
		if (isset($options['name']))
		{
			$this->setName($options['name']);
		}

		// Set id
		if (isset($options['id']))
		{
			$this->setId($options['id']);
		}

		// Set expire time
		if (isset($options['expire']))
		{
			$this->expire = $options['expire'];
		}

		// Get security options
		if (isset($options['security']))
		{
			$this->security = explode(',', $options['security']);
		}

		if (isset($options['force_ssl']))
		{
			$this->force_ssl = (bool) $options['force_ssl'];
		}

		if (isset($options['cookie_domain']))
		{
			$this->cookie_domain = $options['cookie_domain'];
		}

		if (isset($options['cookie_path']))
		{
			$this->cookie_path = $options['cookie_path'];
		}

		if (isset($options['prefix']))
		{
			$this->prefix = $options['prefix'];
		}

		if (isset($options['input']) && $options['input'] instanceof Input)
		{
			$this->input = $options['input'];
		}

		if (isset($options['dispatcher']) && $options['dispatcher'] instanceof DispatcherInterface)
		{
			$this->dispatcher = $options['dispatcher'];
		}

		// Sync the session maxlifetime
		ini_set('session.gc_maxlifetime', $this->expire);

		return true;
	}

	/**
	 * Do some checks for security reason
	 *
	 * - timeout check (expire)
	 * - ip-fixiation
	 * - browser-fixiation
	 *
	 * If one check failed, session data has to be cleaned.
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  boolean  True on success
	 *
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   1.0
	 */
	protected function validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->state = 'active';

			$this->set('session.client.address', null);
			$this->set('session.client.forwarded', null);
			$this->set('session.client.browser', null);
		}

		// Check if session has expired
		if ($this->expire)
		{
			$curTime = $this->get('session.timer.now', 0);
			$maxTime = $this->get('session.timer.last', 0) + $this->expire;

			// Empty session variables
			if ($maxTime < $curTime)
			{
				$this->state = 'expired';

				return false;
			}
		}

		// Record proxy forwarded for in the session in case we need it later
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		// Check for client address
		if (in_array('fix_address', $this->security) && isset($_SERVER['REMOTE_ADDR']))
		{
			$ip = $this->get('session.client.address');

			if ($ip === null)
			{
				$this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
			}
			elseif ($_SERVER['REMOTE_ADDR'] !== $ip)
			{
				$this->state = 'error';

				return false;
			}
		}

		// Check for clients browser
		if (in_array('fix_browser', $this->security) && isset($_SERVER['HTTP_USER_AGENT']))
		{
			$browser = $this->get('session.client.browser');

			if ($browser === null)
			{
				$this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);
			}
			elseif ($_SERVER['HTTP_USER_AGENT'] !== $browser)
			{
				// @todo remove code: 				$this->_state	=	'error';
				// @todo remove code: 				return false;
			}
		}

		return true;
	}
}
