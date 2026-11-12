<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Validator;

use Joomla\Input\Input;
use Joomla\Session\Session;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\Validator\ForwardedValidator;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Validator\ForwardedValidator.
 */
#[BackupGlobals(true)]
class ForwardedValidatorTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = new Session(new RuntimeStorage());
        $this->session->start();
    }

    public function testTheForwardedAddressIsRecorded()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

        (new ForwardedValidator(new Input([]), $this->session))->validate();

        $this->assertSame('198.51.100.7', $this->session->get('session.client.forwarded'));
    }

    public function testNothingIsRecordedWithoutTheHeader()
    {
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        (new ForwardedValidator(new Input([]), $this->session))->validate();

        $this->assertNull($this->session->get('session.client.forwarded'));
    }

    public function testARestartClearsTheRecordedAddress()
    {
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->session->set('session.client.forwarded', '198.51.100.7');

        (new ForwardedValidator(new Input([]), $this->session))->validate(true);

        $this->assertNull($this->session->get('session.client.forwarded'));
    }
}
