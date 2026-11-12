<?php

/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Session\HandlerInterface;

/**
 * Wincache session storage handler
 *
 * @since       2.0.0
 * @deprecated  5.0 The wincache extension is abandoned and has no PHP 8 builds; use another session handler instead.
 */
class WincacheHandler extends \SessionHandler implements HandlerInterface
{
    /**
     * Constructor
     *
     * @since   2.0.0
     */
    public function __construct()
    {
        trigger_deprecation(
            'joomla/session',
            '__DEPLOY_VERSION__',
            'The %s class is deprecated and will be removed in 5.0, as the wincache extension is abandoned. Use another session handler instead.',
            self::class
        );

        if (!headers_sent()) {
            ini_set('session.save_handler', 'wincache');
        }
    }

    /**
     * Test to see if the HandlerInterface is available
     *
     * @return  boolean  True on success, false otherwise
     *
     * @since   2.0.0
     */
    public static function isSupported(): bool
    {
        return \extension_loaded('wincache') && \function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
    }
}
