<?php

/**
 * This file contains the PushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Environment;
use ApnsPHP\Message;
use ApnsPHP\Push;
use Lunr\Halo\LunrBaseTest;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var LoggerInterface&MockObject
     */
    protected $logger;

    /**
     * Mock instance of the Message class.
     * @var Message&MockObject
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
            Environment::Sandbox,
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
