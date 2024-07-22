<?php

/**
 * APNS Environment
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP;

/**
 * APNS Environment.
 */
enum Environment
{
    /**
     * Production environment.
     */
    case Production;

    /**
     * Production environment using alternative port.
     */
    case AltProduction;

    /**
     * Sandbox environment.
     */
    case Sandbox;

    /**
     * Sandbox environment using alternative port.
     */
    case AltSandbox;

    /**
     * Get HTTP URL for a given environment
     *
     * @return string The URL to the matching APNS environment
     */
    public function getUrl(): string
    {
        return match ($this) {
            Environment::Production    => 'https://api.push.apple.com:443',
            Environment::AltProduction => 'https://api.push.apple.com:2197',
            Environment::Sandbox       => 'https://api.sandbox.push.apple.com:443',
            Environment::AltSandbox    => 'https://api.sandbox.push.apple.com:2197',
        };
    }
}
