<?php

/**
 * @file
 * Push class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

namespace ApnsPHP;

use DateTimeImmutable;
use ApnsPHP\Push\Exception;
use Psr\Log\LoggerInterface;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Configuration;

/**
 * The Push Notification Provider.
 *
 * The class manages a message queue and sends notifications payload to Apple Push
 * Notification Service.
 */
class Push
{
    /**< @type integer Production environment. */
    public const ENVIRONMENT_PRODUCTION = 0;

    /**< @type integer Sandbox environment. */
    public const ENVIRONMENT_SANDBOX = 1;

    /**< @type integer Device token length. */
    public const DEVICE_BINARY_SIZE = 32;

    /**< @type integer Default write interval in micro seconds. */
    public const WRITE_INTERVAL = 10000;

    /**< @type integer Default connect retry interval in micro seconds. */
    public const CONNECT_RETRY_INTERVAL = 1000000;

    /**< @type integer Default socket select timeout in micro seconds. */
    public const SOCKET_SELECT_TIMEOUT = 1000000;

    /**< @type integer Payload command. */
    protected const COMMAND_PUSH = 1;

    /**< @type integer Error-response packet size. */
    protected const ERROR_RESPONSE_SIZE = 6;

    /**< @type integer Error-response command code. */
    protected const ERROR_RESPONSE_COMMAND = 8;

    /**< @type integer Status code for internal error (not Apple). */
    protected const STATUS_CODE_INTERNAL_ERROR = 999;

    /**< @type integer Active environment. */
    protected int $environment;

    /**< @type integer Connect timeout in seconds. */
    protected int $connectTimeout;

    /**< @type integer Connect retry times. */
    protected int $connectRetryTimes = 3;

    /**< @type string Provider certificate file with key (Bundled PEM). */
    protected string $providerCertFile;

    /**< @type string Provider certificate passphrase. */
    protected string $providerCertPassphrase;

    /**< @type string|null Provider Authentication token. */
    protected ?string $providerToken;

    /**< @type string|null Apple Team Identifier. */
    protected ?string $providerTeamId;

    /**< @type string|null Apple Key Identifier. */
    protected ?string $providerKeyId;

    /**< @type string Root certification authority file. */
    protected string $rootCertAuthorityFile;

    /**< @type integer Write interval in micro seconds. */
    protected int $writeInterval;

    /**< @type integer Connect retry interval in micro seconds. */
    protected int $connectRetryInterval;

    /**< @type integer Socket select timeout in micro seconds. */
    protected int $socketSelectTimeout;

    /**< @type Psr\Log\LoggerInterface Logger. */
    protected LoggerInterface $logger;

    /**< @type resource SSL Socket. */
    protected $hSocket;    /**< @type array HTTP/2 Error-response messages. */
    protected array $HTTPErrorResponseMessages = array(
        200 => 'Success',
        400 => 'Bad request',
        403 => 'There was an error with the certificate',
        405 => 'The request used a bad :method value. Only POST requests are supported',
        410 => 'The device token is no longer active for the topic',
        413 => 'The notification payload was too large',
        429 => 'The server received too many requests for the same device token',
        500 => 'Internal server error',
        503 => 'The server is shutting down and unavailable',
        self::STATUS_CODE_INTERNAL_ERROR => 'Internal error'
    );

    /**< @type integer Send retry times. */
    protected int $sendRetryTimes = 3;

    /**< @type array HTTP/2 Service URLs environments. */
    protected array $HTTPServiceURLs = array(
        'https://api.push.apple.com:443', // Production environment
        'https://api.development.push.apple.com:443' // Sandbox environment
    );

    /**< @type array Message queue. */
    protected array $messageQueue = array();

    /**< @type array Error container. */
    protected array $errors = array();

    /**
     * Constructor.
     *
     * @param  $environment @type integer Environment.
     * @param  $providerCertificateFile @type string Provider certificate file
     *         with key (Bundled PEM).
     * @param  $logger $type LoggerInterface A Logger implementing PSR-3
     */
    public function __construct(int $environment, string $providerCertificateFile, LoggerInterface $logger)
    {
        if ($environment != self::ENVIRONMENT_PRODUCTION && $environment != self::ENVIRONMENT_SANDBOX) {
            throw new Exception(
                "Invalid environment '{$environment}'"
            );
        }
        $this->environment = $environment;

        if (!is_readable($providerCertificateFile)) {
            throw new Exception(
                "Unable to read certificate file '{$providerCertificateFile}'"
            );
        }
        $this->providerCertFile = $providerCertificateFile;

        $this->connectTimeout = ini_get("default_socket_timeout");
        $this->writeInterval = self::WRITE_INTERVAL;
        $this->connectRetryInterval = self::CONNECT_RETRY_INTERVAL;
        $this->socketSelectTimeout = self::SOCKET_SELECT_TIMEOUT;

        $this->logger = $logger;
    }

    /**
     * Set the send retry times value.
     *
     * If the client is unable to send a payload to to the server retries at least
     * for this value. The default send retry times is 3.
     *
     * @param  $retryTimes @type integer Send retry times.
     */
    public function setSendRetryTimes(int $retryTimes): void
    {
        $this->sendRetryTimes = (int)$retryTimes;
    }

    /**
     * Get the send retry time value.
     *
     * @return @type integer Send retry times.
     */
    public function getSendRetryTimes(): int
    {
        return $this->sendRetryTimes;
    }

    /**
     * Set the Provider Certificate passphrase.
     *
     * @param string $providerCertPassphrase Provider Certificate passphrase.
     */
    public function setProviderCertificatePassphrase(string $providerCertPassphrase): void
    {
        $this->providerCertPassphrase = $providerCertPassphrase;
    }

    /**
     * Set the Team Identifier.
     *
     * @param string $teamId Apple Team Identifier.
     */
    public function setTeamId(string $teamId): void
    {
        $this->providerTeamId = $teamId;
    }

    /**
     * Set the Key Identifier.
     *
     * @param string $keyId Apple Key Identifier.
     */
    public function setKeyId(string $keyId): void
    {
        $this->providerKeyId = $keyId;
    }

    /**
     * Set the Root Certification Authority file.
     *
     * Setting the Root Certification Authority file automatically set peer verification
     * on connect.
     *
     * @see http://tinyurl.com/GeneralProviderRequirements
     * @see http://www.entrust.net/
     * @see https://www.entrust.net/downloads/root_index.cfm
     *
     * @param  $rootCertificationAuthorityFile @type string Root Certification
     *         Authority file.
     */
    public function setRootCertificationAuthority(string $rootCertificationAuthorityFile): void
    {
        if (!is_readable($rootCertificationAuthorityFile)) {
            throw new Exception(
                "Unable to read Certificate Authority file '{$rootCertificationAuthorityFile}'"
            );
        }
        $this->rootCertAuthorityFile = $rootCertificationAuthorityFile;
    }

    /**
     * Get the Root Certification Authority file path.
     *
     * @return @type string Current Root Certification Authority file path.
     */
    public function getCertificateAuthority(): string
    {
        return $this->rootCertAuthorityFile;
    }

    /**
     * Set the write interval.
     *
     * After each socket write operation we are sleeping for this
     * time interval. To speed up the sending operations, use Zero
     * as parameter but some messages may be lost.
     *
     * @param  $writeInterval @type integer Write interval in micro seconds.
     */
    public function setWriteInterval(int $writeInterval): void
    {
        $this->writeInterval = $writeInterval;
    }

    /**
     * Get the write interval.
     *
     * @return @type integer Write interval in micro seconds.
     */
    public function getWriteInterval(): int
    {
        return $this->writeInterval;
    }

    /**
     * Set the connection timeout.
     *
     * The default connection timeout is the PHP internal value "default_socket_timeout".
     * @see http://php.net/manual/en/filesystem.configuration.php
     *
     * @param  $timeout @type integer Connection timeout in seconds.
     */
    public function setConnectTimeout(int $timeout): void
    {
        $this->connectTimeout = $timeout;
    }

    /**
     * Get the connection timeout.
     *
     * @return @type integer Connection timeout in seconds.
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * Set the connect retry times value.
     *
     * If the client is unable to connect to the server retries at least for this
     * value. The default connect retry times is 3.
     *
     * @param  $retryTimes @type integer Connect retry times.
     */
    public function setConnectRetryTimes(int $retryTimes): void
    {
        $this->connectRetryTimes = $retryTimes;
    }

    /**
     * Get the connect retry time value.
     *
     * @return @type integer Connect retry times.
     */
    public function getConnectRetryTimes(): int
    {
        return $this->connectRetryTimes;
    }

    /**
     * Set the connect retry interval.
     *
     * If the client is unable to connect to the server retries at least for ConnectRetryTimes
     * and waits for this value between each attempts.
     *
     * @param  $retryInterval @type integer Connect retry interval in micro seconds.
     *@see setConnectRetryTimes
     *
     */
    public function setConnectRetryInterval(int $retryInterval): void
    {
        $this->connectRetryInterval = $retryInterval;
    }

    /**
     * Get the connect retry interval.
     *
     * @return @type integer Connect retry interval in micro seconds.
     */
    public function getConnectRetryInterval(): int
    {
        return $this->connectRetryInterval;
    }

    /**
     * Set the TCP socket select timeout.
     *
     * After writing to socket waits for at least this value for read stream to
     * change status.
     *
     * In Apple Push Notification protocol there isn't a real-time
     * feedback about the correctness of notifications pushed to the server; so after
     * each write to server waits at least SocketSelectTimeout. If, during this
     * time, the read stream change its status and socket received an end-of-file
     * from the server the notification pushed to server was broken, the server
     * has closed the connection and the client needs to reconnect.
     *
     * @see http://php.net/stream_select
     *
     * @param  $selectTimeout @type integer Socket select timeout in micro seconds.
     */
    public function setSocketSelectTimeout(int $selectTimeout): void
    {
        $this->socketSelectTimeout = $selectTimeout;
    }

    /**
     * Get the TCP socket select timeout.
     *
     * @return @type integer Socket select timeout in micro seconds.
     */
    public function getSocketSelectTimeout(): int
    {
        return $this->socketSelectTimeout;
    }

    /**
     * Connects to Apple Push Notification service server.
     *
     * Retries ConnectRetryTimes if unable to connect and waits setConnectRetryInterval
     * between each attempts.
     *
     * @see setConnectRetryInterval
     * @see setConnectRetryTimes
     */
    public function connect(): void
    {
        $connected = false;
        $retry = 0;
        while (!$connected) {
            try {
                $connected = $this->httpInit();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                if ($retry >= $this->connectRetryTimes) {
                    throw $e;
                } else {
                    $this->logger->info(
                        "Retry to connect (" . ($retry + 1) .
                        "/{$this->connectRetryTimes})..."
                    );
                    usleep($this->connectRetryInterval);
                }
            }
            $retry++;
        }
    }

    /**
     * Disconnects from Apple Push Notifications service server.
     *
     * @return @type boolean True if successful disconnected.
     */
    public function disconnect(): bool
    {
        if (is_resource($this->hSocket) || is_object($this->hSocket)) {
            $this->logger->info('Disconnected.');
            curl_close($this->hSocket);
            return true;
        }
        return false;
    }

    /**
     * Initializes cURL, the HTTP/2 backend used to connect to Apple Push Notification
     * service server via HTTP/2 API protocol.
     *
     * @return @type boolean True if successful initialized.
     */
    protected function httpInit(): bool
    {
        $this->logger->info("Trying to initialize HTTP/2 backend...");

        $this->hSocket = curl_init();
        if ($this->hSocket === false) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }
        $curlOpts = array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'ApnsPHP',
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_VERBOSE => false
        );

        if (strpos($this->providerCertFile, '.pem') !== false) {
            $this->logger->info("Initializing HTTP/2 backend with certificate.");
            $curlOpts[CURLOPT_SSLCERT] = $this->providerCertFile;
            $curlOpts[CURLOPT_SSLCERTPASSWD] = empty($this->providerCertPassphrase) ?
                null : $this->providerCertPassphrase;
        }

        if (strpos($this->providerCertFile, '.p8') !== false) {
            $this->logger->info("Initializing HTTP/2 backend with key.");
            $this->providerToken = $this->getJsonWebToken();
        }

        if (!curl_setopt_array($this->hSocket, $curlOpts)) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        $this->logger->info("Initialized HTTP/2 backend.");

        return true;
    }

    /**
     * @return string
     */
    protected function getJsonWebToken(): string
    {
        $key = InMemory::file($this->providerCertFile);
        return Configuration::forUnsecuredSigner()->builder()
            ->issuedBy($this->providerTeamId)
            ->issuedAt(new DateTimeImmutable())
            ->withHeader('kid', $this->providerKeyId)
            ->getToken(Sha256::create(), $key)
            ->toString();
    }

    /**
     * Adds a message to the message queue.
     *
     * @param  $message @type ApnsPHPMessage The message.
     */
    public function add(Message $message): void
    {
        $messagePayload = $message->getPayload();
        $recipients = $message->getRecipientsNumber();

        $messageQueueLen = count($this->messageQueue);
        for ($i = 0; $i < $recipients; $i++) {
            $messageId = $messageQueueLen + $i + 1;
            $messages = array(
                'MESSAGE' => $message->selfForRecipient($i),
                'ERRORS' => array()
            );
            $this->messageQueue[$messageId] = $messages;
        }
    }

    /**
     * Sends all messages in the message queue to Apple Push Notification Service.
     */
    public function send(): void
    {
        if (!$this->hSocket) {
            throw new Exception(
                'Not connected to Push Notification Service'
            );
        }

        if (empty($this->messageQueue)) {
            throw new Exception(
                'No notifications queued to be sent'
            );
        }

        $this->errors = array();
        $run = 1;
        while (($messageAmount = count($this->messageQueue)) > 0) {
            $this->logger->info("Sending messages queue, run #{$run}: $messageAmount message(s) left in queue.");

            $error = false;
            foreach ($this->messageQueue as $key => &$messages) {
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                $message = $messages['MESSAGE'];
                $customIdentifier = (string)$message->getCustomIdentifier();
                $customIdentifier = sprintf(
                    '[custom identifier: %s]',
                    empty($customIdentifier) ? 'unset' : $customIdentifier
                );

                $errorAmount = 0;
                if (!empty($messages['ERRORS'])) {
                    foreach ($messages['ERRORS'] as $errors) {
                        if ($errors['statusCode'] == 0 || $errors['statusCode'] == 200) {
                            $this->logger->info(
                                "Message ID {$key} {$customIdentifier} has no error ({$errors['statusCode']}),
                                 removing from queue..."
                            );
                            $this->removeMessageFromQueue($key);
                            continue 2;
                        } elseif ($errors['statusCode'] > 200 && $errors['statusCode'] <= 413) {
                            $this->logger->warning(
                                "Message ID {$key} {$customIdentifier} has an unrecoverable error
                                 ({$errors['statusCode']}), removing from queue without retrying..."
                            );
                            $this->removeMessageFromQueue($key, true);
                            continue 2;
                        }
                    }
                    if (($errorAmount = count($messages['ERRORS'])) >= $this->sendRetryTimes) {
                        $this->logger->warning(
                            "Message ID {$key} {$customIdentifier} has {$errorAmount} errors, removing from queue..."
                        );
                        $this->removeMessageFromQueue($key, true);
                        continue;
                    }
                }

                $messageBytes = strlen($message->getPayload());
                $this->logger->debug("Sending message ID {$key} {$customIdentifier} (" . ($errorAmount + 1) .
                                        "/{$this->sendRetryTimes}): {$messageBytes} bytes.");

                $errorMessage = null;

                if (!$this->httpSend($message, $reply)) {
                    $errorMessage = array(
                        'identifier' => $key,
                        'statusCode' => curl_getinfo($this->hSocket, CURLINFO_HTTP_CODE),
                        'statusMessage' => $reply
                    );
                }
                usleep($this->writeInterval);

                $error = $this->updateQueue($errorMessage);
                if ($error) {
                    break;
                }
            }

            if (!$error) {
                $this->messageQueue = array();
            }

            $run++;
        }
    }

    /**
     * Send a message using the HTTP/2 API protocol.
     *
     * @param  $message @type ApnsPHPMessage The message.
     * @param  $reply @type string The reply message.
     * @return bool success of API call
     */
    private function httpSend(Message $message, &$reply): bool
    {
        $headers = array('Content-Type: application/json');
        if (!empty($message->getTopic())) {
            $headers[] = sprintf('apns-topic: %s', $message->getTopic());
        }
        if (!empty($message->getExpiry())) {
            $headers[] = sprintf('apns-expiration: %s', $message->getExpiry());
        }
        if (!empty($message->getPriority())) {
            $headers[] = sprintf('apns-priority: %s', $message->getPriority());
        }
        if (!empty($message->getCollapseId())) {
            $headers[] = sprintf('apns-collapse-id: %s', $message->getCollapseId());
        }
        if (!empty($message->getCustomIdentifier())) {
            $headers[] = sprintf('apns-id: %s', $message->getCustomIdentifier());
        }
        if ($message->getPushType() !== null) {
            $headers[] = sprintf('apns-push-type: %s', $message->getPushType());
        }
        if (!empty($this->providerToken)) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->providerToken);
        }

        if (
            !(curl_setopt_array($this->hSocket, array(
            CURLOPT_POST => true,
            CURLOPT_URL => sprintf(
                '%s/3/device/%s',
                $this->HTTPServiceURLs[$this->environment],
                $message->getRecipient()
            ),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $message->getPayload()
            )) && ($reply = curl_exec($this->hSocket)) !== false)
        ) {
            return false;
        }

        return curl_getinfo($this->hSocket, CURLINFO_HTTP_CODE) === 200;
    }

    /**
     * Returns messages in the message queue.
     *
     * When a message is successful sent or reached the maximum retry time is removed
     * from the message queue and inserted in the Errors container. Use the getErrors()
     * method to retrive messages with delivery error(s).
     *
     * @param  $empty @type boolean @optional Empty message queue.
     * @return @type array Array of messages left on the queue.
     */
    public function getMessageQueue(bool $empty = true): array
    {
        $messages = $this->messageQueue;
        if ($empty) {
            $this->messageQueue = array();
        }
        return $messages;
    }

    /**
     * Returns messages not delivered to the end user because one (or more) error
     * occurred.
     *
     * @param  $empty @type boolean @optional Empty message container.
     * @return @type array Array of messages not delivered because one or more errors
     *         occurred.
     */
    public function getErrors(bool $empty = true): array
    {
        $messages = $this->errors;
        if ($empty) {
            $this->errors = array();
        }
        return $messages;
    }

    /**
     * Checks for error message and deletes messages successfully sent from message queue.
     *
     * @param  $errorMessages @type array @optional The error message. It will anyway
     *         always be read from the main stream. The latest successful message
     *         sent is the lowest between this error message and the message that
     *         was read from the main stream.
     * @return @type boolean True if an error was received.
     */
    protected function updateQueue(?array $errorMessages = null): bool
    {
        if (!isset($errorMessages)) {
            return false;
        }

        $this->logger->error('Unable to send message ID ' .
            $errorMessages['identifier'] . ': ' .
            $errorMessages['statusMessage'] . ' (' . $errorMessages['statusCode'] . ').');

        $this->disconnect();

        foreach ($this->messageQueue as $key => &$message) {
            if ($key < $errorMessages['identifier']) {
                unset($this->messageQueue[$key]);
            } elseif ($key == $errorMessages['identifier']) {
                $message['ERRORS'][] = $errorMessages;
            } else {
                break;
            }
        }

        $this->connect();

        return true;
    }

    /**
     * Remove a message from the message queue.
     *
     * @param  $messageId @type integer The Message ID.
     * @param  $error @type boolean @optional Insert the message in the Error container.
     */
    protected function removeMessageFromQueue(int $messageId, bool $error = false): void
    {
        if ($messageId <= 0) {
            throw new Exception(
                'Message ID format is not valid.'
            );
        }
        if (!isset($this->messageQueue[$messageId])) {
            throw new Exception(
                "The Message ID {$messageId} does not exists."
            );
        }
        if ($error) {
            $this->errors[$messageId] = $this->messageQueue[$messageId];
        }
        unset($this->messageQueue[$messageId]);
    }
}
