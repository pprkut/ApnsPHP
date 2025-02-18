<?php

/**
 * SPDX-FileCopyrightText: Copyright 2016 Marco Rocca (marco.rocca@delitestudio.com)
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message;

use ApnsPHP\Message;

/**
 * The SafariMessage Push Notification Message.
 *
 * The class represents a SafariMessage Push Notification message.
 *
 * @phpstan-type PayloadDictionary array{
 *     aps: array{
 *         alert: array{
 *             title?: string,
 *             body?: string,
 *             action?: string,
 *             url-args?: string[],
 *         },
 *     },
 * }
 */
class SafariMessage extends Message
{
    /**
     * The label of the action button, if the user sets the notifications to appear as alerts.
     * @var string
     */
    protected string $action;

    /**
     * Variable string values to appear in place of the format specifiers in urlFormatString.
     * @var string[]
     */
    protected array $urlArgs;

    /**
     * Set the label of the action button, if the user sets the notifications to appear as alerts.
     *
     * @param string $action The label of the action button
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Get the label of the action button, if the user sets the notifications to appear as alerts.
     *
     * @return string The label of the action button
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set the variable string values to appear in place of the format specifiers
     * in urlFormatString.
     *
     * @param string[] $urlArgs The variable string values.
     */
    public function setUrlArgs(array $urlArgs): void
    {
        $this->urlArgs = $urlArgs;
    }

    /**
     * Get the variable string values to appear in place of the format specifiers
     * in urlFormatString.
     *
     * @return string[] The variable string values.
     */
    public function getUrlArgs(): array
    {
        return $this->urlArgs;
    }

    /**
     * Get the payload dictionary.
     *
     * @return PayloadDictionary The payload dictionary.
     */
    protected function getPayloadDictionary(): array
    {
        $payload[self::APPLE_RESERVED_NAMESPACE]['alert'] = [];

        if (isset($this->title)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['title'] = (string)$this->title;
        }

        if (isset($this->text)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['body'] = (string)$this->text;
        }

        if (isset($this->action)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['action'] = (string)$this->action;
        }

        if (isset($this->urlArgs)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['url-args'] = $this->urlArgs;
        }

        return $payload;
    }
}
