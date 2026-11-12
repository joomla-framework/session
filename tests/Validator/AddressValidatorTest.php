<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Validator;

use Joomla\Input\Input;
use Joomla\Session\Exception\InvalidSessionException;
use Joomla\Session\Session;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\Validator\AddressValidator;
use Joomla\Utilities\IpHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Session\Validator\AddressValidator.
 */
class AddressValidatorTest extends TestCase
{
    private Session $session;

    private AddressValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = new Session(new RuntimeStorage());
        $this->session->start();

        $this->validator = new AddressValidator(new Input([]), $this->session);
    }

    protected function tearDown(): void
    {
        IpHelper::setIp(null);

        parent::tearDown();
    }

    public function testTheAddressIsStoredOnTheFirstValidation()
    {
        IpHelper::setIp('192.0.2.1');

        $this->validator->validate();

        $this->assertSame('192.0.2.1', $this->session->get('session.client.address'));
    }

    public function testTheSameAddressIsValid()
    {
        IpHelper::setIp('192.0.2.1');
        $this->session->set('session.client.address', '192.0.2.1');

        $this->validator->validate();

        $this->assertSame('192.0.2.1', $this->session->get('session.client.address'));
    }

    public function testAChangedAddressIsInvalid()
    {
        IpHelper::setIp('192.0.2.2');
        $this->session->set('session.client.address', '192.0.2.1');

        $this->expectException(InvalidSessionException::class);

        $this->validator->validate();
    }

    public function testARestartStoresTheNewAddress()
    {
        IpHelper::setIp('192.0.2.2');
        $this->session->set('session.client.address', '192.0.2.1');

        $this->validator->validate(true);

        $this->assertSame('192.0.2.2', $this->session->get('session.client.address'));
    }

    public function testAnInvalidAddressIsIgnored()
    {
        IpHelper::setIp('not-an-address');

        $this->validator->validate();

        $this->assertNull($this->session->get('session.client.address'));
    }
}
