<?php

/**
 * This file contains the MessageGetPayloadTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the getPayload function
 *
 * @covers \ApnsPHP\Message
 */
class MessageGetPayloadTest extends MessageTest
{
    /**
     * Test that getPayload returns complete JSON encoded payload
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadReturnsCompletePayload(): void
    {
        $this->class->setTitle('Were no strangers to love');
        $this->class->setText('You know the rules, and so do I');
        $this->class->setBadge(1);
        $this->class->setSound('default');
        $this->class->setContentAvailable(true);
        $this->class->setMutableContent(true);
        $this->class->setCategory('something');
        $this->class->setThreadId('thisIsAThreadId');
        $this->class->setCustomProperty('propertie', 'propertie');
        $this->class->setCustomProperty('name', 'value');

        $payload = '{"aps":{"alert":{"title":"Were no strangers to love","body":"You know the rules, and so do I"},' .
                   '"badge":1,"sound":"default","content-available":1,"mutable-content":1,"category":"something",' .
                   '"thread-id":"thisIsAThreadId"},"propertie":"propertie","name":"value"}';

        $result = $this->class->getPayload();

        $this->assertEquals($payload, $result);
    }

    /**
     * Test that getPayload returns empty JSON encoded payload when nothing is set
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadReturnsEmptyPayload(): void
    {
        $result = $this->class->getPayload();

        $this->assertEquals('{"aps":{}}', $result);
    }

    /**
     * Test that getPayload throws an exception when the payload is too long and autoAdjustLongPayload is false.
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadThrowsExceptionOnTooLongPayload(): void
    {
        $this->class->setText($this->getLargeString(4077));
        $this->class->setAutoAdjustLongPayload(false);

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('JSON Payload is too long: 4097 bytes. Maximum size is 4096 bytes');

        $this->class->getPayload();
    }

    /**
     * Test that getPayload throws an exception when the payload is too long and can't be auto adjusted.
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadThrowsExceptionOnTooLongPayloadWithoutAutoAdjust(): void
    {
        $this->class->setTitle($this->getLargeString(4056));
        $this->class->setText($this->getLargeString(20));

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('JSON Payload is too long: 4116 bytes. Maximum size is 4096 bytes.' .
                                      ' The message text can not be auto-adjusted.');

        $this->class->getPayload();
    }

    /**
     * Return a string that is a certain size in bytes
     *
     * @param int $size String size
     *
     * @return string String of certain size in bytes
     */
    private function getLargeString($size)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length     = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $size; $i++) {
            $randomString .= $characters[rand(0, $length - 1)];
        }
        return $randomString;
    }
}
