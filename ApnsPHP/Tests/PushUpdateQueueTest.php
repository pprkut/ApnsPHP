<?php

/**
 * This file contains the PushUpdateQueueTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the updateQueue function
 *
 * @covers \ApnsPHP\Push
 */
class PushUpdateQueueTest extends PushTest
{
    /**
     * Test that updateQueue() returns false if there is no errorMessage
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueReturnsFalse(): void
    {
        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invoke($this->class);

        $this->assertFalse($result);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueSucceedsWithErrorMessageParameter(): void
    {
        $errorMessage = [
            'identifier' => 3,
            'time' => 1620029695,
            'statusCode' => 4 ,
            'statusMessage' => 'Missing payload'
        ];

        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $resultMessage = [ 3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [ $errorMessage ] ]];

        $this->set_reflection_property_value('messageQueue', $queue);
        $this->set_reflection_property_value('logger', $this->logger);

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                     );

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 3: Missing payload (4).');

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invokeArgs($this->class, [ $errorMessage ]);

        $messageQueue  = $this->get_reflection_property_value('messageQueue');

        $this->assertTrue($result);
        $this->assertEquals($resultMessage, $messageQueue);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueDoesNotDeleteUnsentMessages(): void
    {
        $errorMessage = [
            'identifier' => 2,
            'time' => 1620029695,
            'statusCode' => 4 ,
            'statusMessage' => 'Missing payload'
        ];

        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            4 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->set_reflection_property_value('messageQueue', $queue);
        $this->set_reflection_property_value('logger', $this->logger);

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                     );

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 2: Missing payload (4).');

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invoke($this->class, $errorMessage);

        $messageQueue  = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertTrue($result);
        $this->assertEquals([ 3 => $queue[3], 4 => $queue[4] ], $messageQueue);
    }
}
