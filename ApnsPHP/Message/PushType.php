<?php

/**
 * APNS Push Type
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message;

/**
 * APNS Push Type.
 */
enum PushType: string
{
    /**
     * The push type for notifications that trigger a user interaction — for example, an alert, badge, or sound.
     * If you set this push type, the apns-topic header field must use your app’s bundle ID as the topic.
     */
    case Alert = 'alert';

    /**
     * The push type for notifications that deliver content in the background, and don’t trigger any user interactions.
     * If you set this push type, the apns-topic header field must use your app’s bundle ID as the topic.
     */
    case Background = 'background';

    /**
     * The push type for notifications that request a user’s location. If you set this push type, the apns-topic header
     * field must use your app’s bundle ID with `.location-query` appended to the end.
     */
    case Location = 'location';

    /**
     * The push type for notifications that provide information about an incoming Voice-over-IP (VoIP) call.
     * If you set this push type, the apns-topic header field must use your app’s bundle ID with `.voip` appended
     * to the end.
     */
    case Voip = 'voip';

    /**
     * The push type for notifications that contain update information for a watchOS app’s complications.
     * If you set this push type, the apns-topic header field must use your app’s bundle ID with `.complication`
     * appended to the end.
     */
    case Complication = 'complication';

    /**
     * The push type to signal changes to a File Provider extension. If you set this push type, the apns-topic header
     * field must use your app’s bundle ID with `.pushkit.fileprovider` appended to the end.
     */
    case FileProvider = 'fileprovider';

    /**
     * The push type for notifications that tell managed devices to contact the MDM server. If you set this push type,
     * you must use the topic from the UID attribute in the subject of your MDM push certificate.
     */
    case Mdm = 'mdm';

    /**
     * The push type to signal changes to a live activity session. If you set this push type, the apns-topic header
     * field must use your app’s bundle ID with `.push-type.liveactivity` appended to the end.
     */
    case LiveActivity = 'liveactivity';

    /**
     * The push type for notifications that provide information about updates to your application’s push to talk
     * services. If you set this push type, the apns-topic header field must use your app’s bundle ID with `.voip-ptt`
     * appended to the end.
     */
    case PushToTalk = 'pushtotalk';
}
