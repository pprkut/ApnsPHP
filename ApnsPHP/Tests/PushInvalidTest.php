<?php

/**
 * This file contains the PushInvalidTest class.
 *
 * @package ApnsPHP
 * @author  Heinz Wiesinger <heinz.wiesinger@moveagency.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Push;

/**
 * This class contains tests for exceptions in case of invalid constructor args
 *
 * @covers \ApnsPHP\Push
 */
class PushInvalidTest extends PushTest
{
    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    }

    /**
     * Test that trying to instantiate the Push class with an invalid environment throws an exception.
     *
     * @covers \ApnsPHP\Push::__construct
     */
    public function testConstructWithInvalidEnvironment()
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage("Invalid environment '3'");

        $this->class = new Push(3, 'server_certificates_bundle_sandbox.pem', $this->logger);
    }

    /**
     * Test that trying to instantiate the Push class with an non-existing provider certificate file
     * throws an exception.
     *
     * @covers \ApnsPHP\Push::__construct
     */
    public function testConstructWithNonExistingProviderCertificateFile()
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage("Unable to read certificate file 'server_certificates_bundle_invalid.pem'");

        $this->class = new Push(0, 'server_certificates_bundle_invalid.pem', $this->logger);
    }

    /**
     * Test that trying to instantiate the Push class with an unreadable provider certificate file throws an exception.
     *
     * @covers \ApnsPHP\Push::__construct
     */
    public function testConstructWithUnreadableProviderCertificateFile()
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage("Unable to read certificate file 'server_certificates_bundle_unreadable.pem'");

        $this->class = new Push(0, 'server_certificates_bundle_unreadable.pem', $this->logger);
    }
}
