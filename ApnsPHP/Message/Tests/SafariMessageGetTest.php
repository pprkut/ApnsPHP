<?php

/**
 * This file contains the SafariMessageGetTest class.
 *
 * @package ApnsPHP
 * @author  Heinz Wiesinger <heinz.wiesinger@moveagency.com>
 */

namespace ApnsPHP\Message\Tests;

/**
 * This class contains tests for the getter functions
 *
 * @covers \ApnsPHP\Message\SafariMessage
 */
class SafariMessageGetTest extends SafariMessageTest
{
    /**
     * Test that getAction() gets the label of the action button.
     *
     * @covers \ApnsPHP\Message\SafariMessage::getAction
     */
    public function testGetAction()
    {
        $this->set_reflection_property_value('action', 'My Action');

        $value = $this->class->getAction();

        $this->assertSame('My Action', $value);
    }

    /**
     * Test that getUrlArgs() gets the URL args.
     *
     * @covers \ApnsPHP\Message\SafariMessage::getUrlArgs
     */
    public function testGetUrlArgs()
    {
        $this->set_reflection_property_value('urlArgs', [ 'args' ]);

        $value = $this->class->getUrlArgs();

        $this->assertSame([ 'args' ], $value);
    }
}
