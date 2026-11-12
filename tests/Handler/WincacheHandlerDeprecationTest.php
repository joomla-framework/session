<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\WincacheHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the deprecation of Joomla\Session\Handler\WincacheHandler, independent of the wincache extension.
 */
class WincacheHandlerDeprecationTest extends TestCase
{
    public function testTheHandlerIsDeprecated()
    {
        $saveHandler = ini_get('session.save_handler');
        $errors      = [];

        set_error_handler(
            static function (int $level, string $message) use (&$errors): bool {
                $errors[] = [$level, $message];

                return true;
            }
        );

        try {
            new WincacheHandler();
        } finally {
            restore_error_handler();
            ini_set('session.save_handler', $saveHandler);
        }

        $this->assertContains(
            [
                E_USER_DEPRECATED,
                'Since joomla/session __DEPLOY_VERSION__: The ' . WincacheHandler::class . ' class is deprecated and will be removed in 5.0,'
                . ' as the wincache extension is abandoned. Use another session handler instead.',
            ],
            $errors
        );
    }
}
