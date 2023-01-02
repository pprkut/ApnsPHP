<?php

/**
 * This file contains the PushDisconnectTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the disconnect function
 *
 * @covers \ApnsPHP\Push
 */
class PushDisconnectTest extends PushTest
{
    /**
     * Test that disconnect() disconnects successfully
     *
     * @covers \ApnsPHP\Push::disconnect
     */
    public function testDisconnectSuccess()
    {
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('logger', $this->logger);

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Disconnected.');

        $result =  $this->class->disconnect();

        $this->assertTrue($result);
    }

    /**
     * Test that disconnect() disconnects returns false if it isn't connected to begin with
     *
     * @covers \ApnsPHP\Push::disconnect
     */
    public function testDisconnectReturnsFalse()
    {
        $result = $this->class->disconnect();

        $this->assertFalse($result);
    }
}
