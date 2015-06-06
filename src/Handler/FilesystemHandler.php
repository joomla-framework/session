<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Filesystem\Folder;
use Joomla\Session\HandlerInterface;

/**
 * Filesystem session storage handler
 *
 * @since  __DEPLOY_VERSION__
 */
class FilesystemHandler extends \SessionHandler implements HandlerInterface
{
	/**
	 * Constructor
	 *
	 * @param   string  $path  Path of directory to save session files.  Leave null to use the PHP configured path.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($path = null)
	{
		if (null === $path)
		{
			$path = ini_get('session.save_path');
		}

		$baseDir = $path;

		if ($count = substr_count($path, ';'))
		{
			if ($count > 2)
			{
				throw new \InvalidArgumentException(sprintf('Invalid argument $path "%s"', $path));
			}

			// Characters after the last semi-colon are the path
			$baseDir = ltrim(strrchr($path, ';'), ';');
		}

		// Create the directory if it doesn't exist
		if (!is_dir($baseDir))
		{
			Folder::create($baseDir);
		}

		ini_set('session.save_path', $path);
		ini_set('session.save_handler', 'files');
	}

	/**
	 * Test to see if the HandlerInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return class_exists('Joomla\\Filesystem\\Folder');
	}
}
