<?php

/**
 * @file
 * SharedConfig class definition.
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

/**
 * @mainpage
 *
 * @li ApnsPHP on GitHub: https://github.com/immobiliare/ApnsPHP
 */

namespace ApnsPHP;

use DateTimeImmutable;
use ApnsPHP\Log\EmbeddedLogger;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Log\LoggerInterface;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Configuration;

/**
 * Abstract class: this is the superclass for all Apple Push Notification Service
 * classes.
 *
 * This class is responsible for the connection to the Apple Push Notification Service
 * and Feedback.
 *
 * @see http://tinyurl.com/ApplePushNotificationService
 */
abstract class SharedConfig
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

    /**< @type array Container for HTTP/2 service URLs environments. */
    protected array $HTTPServiceURLs = array();

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
    protected $hSocket;

    /**
     * Constructor.
     *
     * @param  $environment @type integer Environment.
     * @param  $providerCertificateFile @type string Provider certificate file
     *         with key (Bundled PEM).
     */
    public function __construct(int $environment, string $providerCertificateFile)
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
    }

    /**
     * Set the Logger instance to use for logging purpose.
     *
     * The default logger is EmbeddedLogger, an instance
     * of LoggerInterface that simply print to standard
     * output log messages.
     *
     * To set a custom logger you have to implement LoggerInterface
     * and use setLogger, otherwise standard logger will be used.
     *
     * @param  $logger @type LoggerInterface Logger instance.
     * @see Psr\Log\LoggerInterface
     * @see EmbeddedLogger
     *
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the Logger instance.
     *
     * @return @type Psr\Log\LoggerInterface Current Logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
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
                $this->logger()->error($e->getMessage());
                if ($retry >= $this->connectRetryTimes) {
                    throw $e;
                } else {
                    $this->logger()->info(
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
            $this->logger()->info('Disconnected.');
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
        $this->logger()->info("Trying to initialize HTTP/2 backend...");

        $this->hSocket = curl_init();
        if (!$this->hSocket) {
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
            $this->logger()->info("Initializing HTTP/2 backend with certificate.");
            $curlOpts[CURLOPT_SSLCERT] = $this->providerCertFile;
            $curlOpts[CURLOPT_SSLCERTPASSWD] = empty($this->providerCertPassphrase) ?
                null : $this->providerCertPassphrase;
        }

        if (strpos($this->providerCertFile, '.p8') !== false) {
            $this->logger()->info("Initializing HTTP/2 backend with key.");
            $this->providerToken = $this->getJsonWebToken();
        }

        if (!curl_setopt_array($this->hSocket, $curlOpts)) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        $this->logger()->info("Initialized HTTP/2 backend.");

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
     * Return the Logger (with lazy loading)
     */
    protected function logger(): LoggerInterface
    {
        if (!isset($this->logger)) {
            $this->logger = new EmbeddedLogger();
        }

        return $this->logger;
    }
}
