<?php

/**
 * This file contains the PushTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Message;
use ApnsPHP\Push;
use Lunr\Halo\LunrBaseTest;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Push class.
 *
 * @covers \ApnsPHP\Push
 */
abstract class PushTest extends LunrBaseTest
{
    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mock instance of the Message class.
     * @var Message
     */
    protected $message;

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->message = $this->getMockBuilder('ApnsPHP\Message')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->class = new Push(
            Push::ENVIRONMENT_SANDBOX,
            'server_certificates_bundle_sandbox.pem',
            $this->logger,
        );

        $this->reflection = new ReflectionClass('ApnsPHP\Push');
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->reflection);
        unset($this->logger);
        unset($this->message);
    }
}
