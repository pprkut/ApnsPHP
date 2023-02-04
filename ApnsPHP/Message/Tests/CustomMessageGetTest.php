<?php

/**
 * This file contains the CustomMessageGetTest class.
 *
 * @package ApnsPHP
 * @author  Heinz Wiesinger <heinz.wiesinger@moveagency.com>
 */

namespace ApnsPHP\Message\Tests;

/**
 * This class contains tests for the getter functions
 *
 * @covers \ApnsPHP\Message\CustomMessage
 */
class CustomMessageGetTest extends CustomMessageTest
{
    /**
     * Test that getActionLocKey() gets the view button title.
     *
     * @covers \ApnsPHP\Message\CustomMessage::getActionLocKey
     */
    public function testGetActionLocKey()
    {
        $this->set_reflection_property_value('actionLocKey', 'My Action');

        $value = $this->class->getActionLocKey();

        $this->assertSame('My Action', $value);
    }

    /**
     * Test that getLocKey() gets the alert message string.
     *
     * @covers \ApnsPHP\Message\CustomMessage::getLocKey
     */
    public function testGetLocKey()
    {
        $this->set_reflection_property_value('locKey', 'My Alert');

        $value = $this->class->getLocKey();

        $this->assertSame('My Alert', $value);
    }

    /**
     * Test that getLocArgs() gets the format strings.
     *
     * @covers \ApnsPHP\Message\CustomMessage::getLocArgs
     */
    public function testGetLocArgs()
    {
        $this->set_reflection_property_value('locArgs', [ 'args' ]);

        $value = $this->class->getLocArgs();

        $this->assertSame([ 'args' ], $value);
    }

    /**
     * Test that getLaunchImage() gets the file name of the launch image.
     *
     * @covers \ApnsPHP\Message\CustomMessage::getLaunchImage
     */
    public function testGetLaunchImage()
    {
        $this->set_reflection_property_value('launchImage', 'my-image');

        $value = $this->class->getLaunchImage();

        $this->assertSame('my-image', $value);
    }

    /**
     * Test that getSubTitle() gets the secondary description.
     *
     * @covers \ApnsPHP\Message\CustomMessage::getSubTitle
     */
    public function testGetSubTitle()
    {
        $this->set_reflection_property_value('subTitle', 'My amazing notification');

        $value = $this->class->getSubTitle();

        $this->assertSame('My amazing notification', $value);
    }
}
