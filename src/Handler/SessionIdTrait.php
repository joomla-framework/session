<?php

/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

/**
 * Implements \SessionIdInterface::create_sid() and \SessionUpdateTimestampHandlerInterface::updateTimestamp() for session handlers
 *
 * PHP 8.6 deprecates passing a handler without create_sid() and validateId() to session_set_save_handler(), and PHP 9 requires
 * them. Classes using this trait implement validateId() themselves, as it depends on the storage.
 *
 * @since  __DEPLOY_VERSION__
 */
trait SessionIdTrait
{
    /**
     * Create a new session ID
     *
     * The ID honours the configured session.sid_length and session.sid_bits_per_character. session_create_id() cannot be used
     * here, as it calls back into the handler.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function create_sid(): string // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- defined by SessionIdInterface
    {
        $length = (int) ini_get('session.sid_length');
        $length = $length >= 22 && $length <= 256 ? $length : 32;

        $bitsPerCharacter = (int) ini_get('session.sid_bits_per_character');
        $bitsPerCharacter = \in_array($bitsPerCharacter, [4, 5, 6], true) ? $bitsPerCharacter : 4;

        // 16, 32 or 64 characters; 256 random byte values map onto them without bias
        $characters = substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,-', 0, 1 << $bitsPerCharacter);
        $mask       = (1 << $bitsPerCharacter) - 1;
        $bytes      = random_bytes($length);
        $id         = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[\ord($bytes[$i]) & $mask];
        }

        return $id;
    }

    /**
     * Update the timestamp of a session whose data did not change
     *
     * Called instead of write() when session.lazy_write is enabled; the data is written again to refresh its lifetime.
     *
     * @param   string  $id    The session ID
     * @param   string  $data  The encoded session data
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        return (bool) $this->write($id, $data);
    }
}
