<?php

/**
 * This file contains the PushSetTest class.
 *
 * @package ApnsPHP
 * @author  Heinz Wiesinger <heinz.wiesinger@moveagency.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the setter functions
 *
 * @covers \ApnsPHP\Push
 */
class PushSetTest extends PushTest
{
    /**
     * Test that setSendRetryTimes() sets how often sends should be retried.
     *
     * @covers \ApnsPHP\Push::setSendRetryTimes
     */
    public function testSetSendRetryTimes()
    {
        $this->class->setSendRetryTimes(4);

        $this->assertPropertySame('sendRetryTimes', 4);
    }

    /**
     * Test that setProviderCertificatePassphrase() sets the passphrase for the provider certificate.
     *
     * @covers \ApnsPHP\Push::setProviderCertificatePassphrase
     */
    public function testSetProviderCertificatePassphrase()
    {
        $this->class->setProviderCertificatePassphrase('password');

        $this->assertPropertySame('providerCertPassphrase', 'password');
    }

    /**
     * Test that setTeamId() sets the provider team ID.
     *
     * @covers \ApnsPHP\Push::setTeamId
     */
    public function testSetTeamId()
    {
        $this->class->setTeamId('team1');

        $this->assertPropertySame('providerTeamId', 'team1');
    }

    /**
     * Test that setKeyId() sets the provider key ID.
     *
     * @covers \ApnsPHP\Push::setKeyId
     */
    public function testSetKeyId()
    {
        $this->class->setKeyId('key1');

        $this->assertPropertySame('providerKeyId', 'key1');
    }

    /**
     * Test that setWriteInterval() sets the write interval.
     *
     * @covers \ApnsPHP\Push::setWriteInterval
     */
    public function testSetWriteInterval()
    {
        $this->class->setWriteInterval(20000);

        $this->assertPropertySame('writeInterval', 20000);
    }

    /**
     * Test that setConnectTimeout() sets the connection timeout.
     *
     * @covers \ApnsPHP\Push::setConnectTimeout
     */
    public function testSetConnectTimeout()
    {
        $this->class->setConnectTimeout(20);

        $this->assertPropertySame('connectTimeout', 20);
    }

    /**
     * Test that setConnectRetryTimes() sets how often connections should be retried.
     *
     * @covers \ApnsPHP\Push::setConnectRetryTimes
     */
    public function testSetConnectRetryTimes()
    {
        $this->class->setConnectRetryTimes(4);

        $this->assertPropertySame('connectRetryTimes', 4);
    }

    /**
     * Test that setConnectRetryInterval() sets the connection retry interval.
     *
     * @covers \ApnsPHP\Push::setConnectRetryInterval
     */
    public function testSetConnectRetryInterval()
    {
        $this->class->setConnectRetryInterval(2000000);

        $this->assertPropertySame('connectRetryInterval', 2000000);
    }
}
