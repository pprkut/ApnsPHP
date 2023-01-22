<?php

/**
 * This file contains the MessageSetTest class.
 *
 * @package ApnsPHP
 * @author  Heinz Wiesinger <heinz.wiesinger@moveagency.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the setter functions
 *
 * @covers \ApnsPHP\Message
 */
class MessageSetTest extends MessageTest
{
    /**
     * Unit test data provider for reserved apple namespace keys.
     *
     * @return array Variations of the reserved apple namespace key
     */
    public function reservedAppleNamespaceKeyProvider()
    {
        $data   = [];
        $data[] = [ 'aps' ];
        $data[] = [ ' aps' ];
        $data[] = [ 'aps ' ];
        $data[] = [ ' aps ' ];

        return $data;
    }

    /**
     * Unit test data provider for valid custom identifiers.
     *
     * @return array Variations of valid custom identifiers
     */
    public function validCustomIdentifierProvider()
    {
        $data   = [];
        $data[] = [ '3491ac4b-0681-4c92-8308-d8d8441f4e64' ];
        $data[] = [ '3491AC4B-0681-4C92-8308-D8D8441F4E64' ];

        return $data;
    }

    /**
     * Unit test data provider for valid message priorities.
     *
     * @return array Variations of a valid message priority
     */
    public function validPriorityProvider()
    {
        $data   = [];
        $data[] = [ 1 ];
        $data[] = [ 5 ];
        $data[] = [ 10 ];

        return $data;
    }

    /**
     * Unit test data provider for valid push types.
     *
     * @return array Variations of a valid push type
     */
    public function validPushTypeProvider()
    {
        $data   = [];
        $data[] = [ 'alert' ];
        $data[] = [ 'background' ];
        $data[] = [ 'location' ];
        $data[] = [ 'voip' ];
        $data[] = [ 'complication' ];
        $data[] = [ 'fileprovider' ];
        $data[] = [ 'mdm' ];
        $data[] = [ 'liveactivity' ];

        return $data;
    }

    /**
     * Test that setText() sets the message text.
     *
     * @covers \ApnsPHP\Message::setText
     */
    public function testSetText()
    {
        $this->class->setText('My Message');

        $this->assertPropertySame('text', 'My Message');
    }

    /**
     * Test that setTitle() sets the message title.
     *
     * @covers \ApnsPHP\Message::setTitle
     */
    public function testSetTitle()
    {
        $this->class->setTitle('My Title');

        $this->assertPropertySame('title', 'My Title');
    }

    /**
     * Test that setBadge() sets the number to badge the application icon with.
     *
     * @covers \ApnsPHP\Message::setBadge
     */
    public function testSetBadge()
    {
        $this->class->setBadge(2);

        $this->assertPropertySame('badge', 2);
    }

    /**
     * Test that setSound() sets sound to play when the message is received.
     *
     * @covers \ApnsPHP\Message::setSound
     */
    public function testSetSound()
    {
        $this->class->setSound('jingle');

        $this->assertPropertySame('sound', 'jingle');
    }

    /**
     * Test that setSound() sets sound to play when the message is received.
     *
     * @covers \ApnsPHP\Message::setSound
     */
    public function testSetDefaultSound()
    {
        $this->class->setSound();

        $this->assertPropertySame('sound', 'default');
    }

    /**
     * Test that setCategory() sets the category of the notification.
     *
     * @covers \ApnsPHP\Message::setCategory
     */
    public function testSetCategory()
    {
        $this->class->setCategory('news');

        $this->assertPropertySame('category', 'news');
    }

    /**
     * Test that setCategory() sets the category of the notification.
     *
     * @covers \ApnsPHP\Message::setCategory
     */
    public function testSetDefaultCategory()
    {
        $this->class->setCategory();

        $this->assertPropertySame('category', '');
    }

    /**
     * Test that setThreadId() sets the thread ID of the notification.
     *
     * @covers \ApnsPHP\Message::setThreadId
     */
    public function testSetThreadId()
    {
        $this->class->setThreadId('news-1');

        $this->assertPropertySame('threadId', 'news-1');
    }

    /**
     * Test that setThreadId() sets the thread ID of the notification.
     *
     * @covers \ApnsPHP\Message::setThreadId
     */
    public function testSetDefaultThreadId()
    {
        $this->class->setThreadId();

        $this->assertPropertySame('threadId', '');
    }

    /**
     * Test that setContentAvailable() sets whether to initiate the newsstand background download.
     *
     * @covers \ApnsPHP\Message::setContentAvailable
     */
    public function testSetContentAvailableTrue()
    {
        $this->class->setContentAvailable(true);

        $this->assertPropertySame('contentAvailable', true);
    }

    /**
     * Test that setContentAvailable() sets whether to initiate the newsstand background download.
     *
     * @covers \ApnsPHP\Message::setContentAvailable
     */
    public function testSetContentAvailableFalse()
    {
        $this->class->setContentAvailable(false);

        $this->assertPropertySame('contentAvailable', null);
    }

    /**
     * Test that setContentAvailable() sets whether to initiate the newsstand background download.
     *
     * @covers \ApnsPHP\Message::setContentAvailable
     */
    public function testSetDefaultContentAvailable()
    {
        $this->class->setContentAvailable(true);

        $this->assertPropertySame('contentAvailable', true);
    }

    /**
     * Test that setMutableContent() sets the mutable-content key.
     *
     * @covers \ApnsPHP\Message::setMutableContent
     */
    public function testSetMutableContentTrue()
    {
        $this->class->setMutableContent(true);

        $this->assertPropertySame('mutableContent', true);
    }

    /**
     * Test that setMutableContent() sets the mutable-content key.
     *
     * @covers \ApnsPHP\Message::setMutableContent
     */
    public function testSetMutableContentFalse()
    {
        $this->class->setMutableContent(false);

        $this->assertPropertySame('mutableContent', null);
    }

    /**
     * Test that setMutableContent() sets the mutable-content key.
     *
     * @covers \ApnsPHP\Message::setMutableContent
     */
    public function testSetDefaultMutableContent()
    {
        $this->class->setMutableContent();

        $this->assertPropertySame('mutableContent', true);
    }

    /**
     * Test that setAutoAdjustLongPayload() sets whether to try to auto-adjust a too long payload.
     *
     * @covers \ApnsPHP\Message::setAutoAdjustLongPayload
     */
    public function testSetAutoAdjustLongPayload()
    {
        $this->class->setAutoAdjustLongPayload(false);

        $this->assertPropertySame('autoAdjustLongPayload', false);
    }

    /**
     * Test that setExpire() sets the time when the message should expire if not already successfully delivered.
     *
     * @covers \ApnsPHP\Message::setExpiry
     */
    public function testSetExpiry()
    {
        $this->class->setExpiry(600);

        $this->assertPropertySame('expiryValue', 600);
    }

    /**
     * Test that setTopic() sets the topic of the notification.
     *
     * @covers \ApnsPHP\Message::setTopic
     */
    public function testSetTopic()
    {
        $this->class->setTopic('My App');

        $this->assertPropertySame('topic', 'My App');
    }

    /**
     * Test that setCollapseId() sets the collapse ID of the notification.
     *
     * @covers \ApnsPHP\Message::setCollapseId
     */
    public function testSetCollapseId()
    {
        $this->class->setCollapseId('news-1');

        $this->assertPropertySame('collapseId', 'news-1');
    }

    /**
     * Test that setCustomProperty() sets a custom property.
     *
     * @covers \ApnsPHP\Message::setCustomProperty
     */
    public function testSetCustomProperty()
    {
        $this->class->setCustomProperty('myId', 'my-news-1');

        $this->assertPropertySame('customProperties', [ 'myId' => 'my-news-1' ]);
    }

    /**
     * Test that setCustomProperty() sets a custom property.
     *
     * @covers \ApnsPHP\Message::setCustomProperty
     */
    public function testSetAdditionalCustomProperty()
    {
        $this->set_reflection_property_value('customProperties', [ 'myId' => 'my-news-1' ]);

        $this->class->setCustomProperty('mySecondId', 'my-sport-news-1');

        $expected = [
            'myId'       => 'my-news-1',
            'mySecondId' => 'my-sport-news-1',
        ];

        $this->assertPropertySame('customProperties', $expected);
    }

    /**
     * Test that setCustomProperty() throws an exception when trying to set a value for a reserved key.
     *
     * @param string $key Reserved key name
     *
     * @dataProvider reservedAppleNamespaceKeyProvider
     * @covers       \ApnsPHP\Message::setCustomProperty
     */
    public function testSetInvalidCustomProperty(string $key)
    {
        $this->expectException('ApnsPHP\Message\Exception');
        $this->expectExceptionMessage("Property name 'aps' can not be used for custom property.");

        $this->class->setCustomProperty($key, 'my-news-1');
    }

    /**
     * Test that setCustomIdentifier() sets a custom identifier.
     *
     * @param string $id Custom identifier value
     *
     * @dataProvider validCustomIdentifierProvider
     * @covers       \ApnsPHP\Message::setCustomIdentifier
     */
    public function testSetValidCustomIndentifier(string $id)
    {
        $this->class->setCustomIdentifier($id);

        $this->assertPropertySame('customIdentifier', $id);
    }

    /**
     * Test that setCustomIdentifier() throws an exception if trying to set an invalid identifier.
     *
     * @covers \ApnsPHP\Message::setCustomIdentifier
     */
    public function testSetInvalidCustomIndentifier()
    {
        $this->expectException('ApnsPHP\Message\Exception');
        $this->expectExceptionMessage('Identifier must be a UUID');

        $this->class->setCustomIdentifier('my-id');
    }

    /**
     * Test that setPriority() sets a message priority.
     *
     * @param int $priority Priority value
     *
     * @dataProvider validPriorityProvider
     * @covers       \ApnsPHP\Message::setPriority
     */
    public function testSetValidPriority(int $priority)
    {
        $this->class->setPriority($priority);

        $this->assertPropertySame('priority', $priority);
    }

    /**
     * Test that setPriority() throws an exception when trying to set an invalid priority.
     *
     * @covers \ApnsPHP\Message::setPriority
     */
    public function testSetInvalidPriority()
    {
        $this->expectException('ApnsPHP\Message\Exception');
        $this->expectExceptionMessage('Invalid priority');

        $this->class->setPriority(2);
    }

    /**
     * Test that setPushType() sets a push type.
     *
     * @param string $type Push type value
     *
     * @dataProvider validPushTypeProvider
     * @covers       \ApnsPHP\Message::setPushType
     */
    public function testSetValidPushType(string $type)
    {
        $this->class->setPushType($type);

        $this->assertPropertySame('pushType', $type);
    }

    /**
     * Test that setPushType() throws an exception when trying to set an invalid push type.
     *
     * @covers \ApnsPHP\Message::setPushType
     */
    public function testSetInvalidPushType()
    {
        $this->expectException('ApnsPHP\Message\Exception');
        $this->expectExceptionMessage('Invalid push type');

        $this->class->setPushType('news');
    }
}
