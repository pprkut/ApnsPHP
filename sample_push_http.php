<?php

/**
 * Push demo
 *
 * phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols, PSR1.Classes.ClassDeclaration.MissingNamespace
 *
 * SPDX-FileCopyrightText: Copyright 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-3-Clause
 */

// Adjust to your timezone
date_default_timezone_set('Europe/Rome');

// Report all PHP errors
error_reporting(-1);

// Using Autoload all classes are loaded on-demand
require_once 'vendor/autoload.php';

class SampleLogger extends \Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        printf("%s: %s ApnsPHP[%d]: %s\n", date('r'), strtoupper($level), getmypid(), trim($message));
    }
}

// Instantiate a new ApnsPHP_Push object
$push = new \ApnsPHP\Push(
    \ApnsPHP\Push::ENVIRONMENT_SANDBOX,
    'UniversalPushNotificationClientSSLCertificate.p8',
    new SampleLogger(),
);

$push->setTeamId('sgfdgfdfgd');
$push->setKeyId('klgjhkojmh75');

// Set the write interval to null for the HTTP/2 Protocol.
$push->setWriteInterval(0);

// Set the Provider Certificate passphrase
// $push->setProviderCertificatePassphrase('test');

// Connect to the Apple Push Notification Service
$push->connect();

// Instantiate a new Message with a single recipient
$message = new \ApnsPHP\Message('19e4d2cb683e6302ff688b0fe9b6f562c40ea5a31a10d593f82b6d6bf1c88678');

// Set the topic of the remote notification (the bundle ID for your app)
$message->setTopic('com.armiento.test');

// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
// over a ApnsPHP_Message object retrieved with the getErrors() message.
$message->setCustomIdentifier('7530A828-E58E-433E-A38F-D8042208CF96');

// Set badge icon to "3"
$message->setBadge(3);

// Set a simple welcome text
$message->setText('Hello APNs-enabled device!');

// Play the default sound
$message->setSound();

// Set a custom property
$message->setCustomProperty('acme2', ['bang', 'whiz']);

// Set another custom property
$message->setCustomProperty('acme3', ['bing', 'bong']);

// Set the expiry value to 30 seconds
$message->setExpiry(30);

// Add the message to the message queue
$push->add($message);

// Send all messages in the message queue
$push->send();

// Disconnect from the Apple Push Notification Service
$push->disconnect();

// Examine the error message container
$aErrorQueue = $push->getErrors();
if (!empty($aErrorQueue)) {
    var_dump($aErrorQueue);
}
