<?php

/**
 * This file contains the PushHttpInitTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Exception;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;

/**
 * This class contains tests for the httpInit function
 *
 * @covers \ApnsPHP\Push
 */
class PushHttpInitTest extends PushTest
{
    /**
     * Test that httpInit() succeeds with certificate
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitSucceedsWithCertificate()
    {
        $this->set_reflection_property_value('providerCertFile', 'cert.pem');
        $this->set_reflection_property_value('logger', $this->logger);

        $message = [
            ['Trying to initialize HTTP/2 backend...'],
            ['Initializing HTTP/2 backend with certificate.'],
            ['Initialized HTTP/2 backend.']
        ];

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(...$message);

        $method = $this->get_accessible_reflection_method('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);
    }

    /**
     * Test that httpInit() succeeds with key
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitSucceedsWithKey()
    {
        $this->set_reflection_property_value('providerCertFile', 'key.p8');
        $this->set_reflection_property_value('providerTeamId', 'TheTeam');
        $this->set_reflection_property_value('providerKeyId', 'TheKey');
        $this->set_reflection_property_value('logger', $this->logger);

        $key = $this->getMockBuilder('Lcobucci\JWT\Signer\Key')
                    ->disableOriginalConstructor()
                    ->getMock();

        $builder = $this->getMockBuilder('Lcobucci\JWT\Builder')
                        ->getMock();

        $token = new Plain(
            new DataSet([ 'headers' ], 'eHeaders'),
            new DataSet([ 'claims' ], 'eClaims'),
            new Signature('signature', 'eSignature'),
        );

        $this->mock_method(
            [ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ],
            function () use ($key) {
                return $key;
            }
        );

        $this->mock_method(
            [ 'Lcobucci\JWT\Configuration', 'builder' ],
            function () use ($builder) {
                return $builder;
            }
        );

        $builder->expects($this->once())
                ->method('issuedBy')
                ->with('TheTeam')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('issuedAt')
                ->with($this->isInstanceof('DateTimeImmutable'))
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('withHeader')
                ->with('kid', 'TheKey')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('getToken')
                ->with($this->isInstanceOf('Lcobucci\JWT\Signer\Ecdsa\Sha256'), $key)
                ->willReturn($token);

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with key.' ],
                         [ 'Initialized HTTP/2 backend.' ]
                     );

        $method = $this->get_accessible_reflection_method('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);

        $this->unmock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ]);
        $this->unmock_method([ 'Lcobucci\JWT\Configuration', 'builder' ]);
    }

    /**
     * Test that httpInit() throws an exception when curl_init() fails
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitThrowsExceptionOnCurlInitFail()
    {
        $this->set_reflection_property_value('logger', $this->logger);

        $this->mock_function('curl_init', function () {
            return false;
        });

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Trying to initialize HTTP/2 backend...');

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $method = $this->get_accessible_reflection_method('httpInit');

        try {
            $method->invoke($this->class);
        } catch (Exception $e) {
            $this->unmock_function('curl_init');

            throw $e;
        }
    }

    /**
     * Test that httpInit() throws an exception when curl_setopt_array() fails
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitThrowsExceptionOnCurlSetoptFail()
    {
        $this->set_reflection_property_value('providerCertFile', 'key.p8');
        $this->set_reflection_property_value('providerTeamId', 'TheTeam');
        $this->set_reflection_property_value('providerKeyId', 'TheKey');
        $this->set_reflection_property_value('logger', $this->logger);

        $this->mock_function('curl_setopt_array', function () {
            return false;
        });

        $key = $this->getMockBuilder('Lcobucci\JWT\Signer\Key')
                    ->disableOriginalConstructor()
                    ->getMock();

        $builder = $this->getMockBuilder('Lcobucci\JWT\Builder')
                        ->getMock();

        $token = new Plain(
            new DataSet([ 'headers' ], 'eHeaders'),
            new DataSet([ 'claims' ], 'eClaims'),
            new Signature('signature', 'eSignature'),
        );

        $this->mock_method(
            [ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ],
            function () use ($key) {
                return $key;
            }
        );

        $this->mock_method(
            [ 'Lcobucci\JWT\Configuration', 'builder' ],
            function () use ($builder) {
                return $builder;
            }
        );

        $builder->expects($this->once())
                ->method('issuedBy')
                ->with('TheTeam')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('issuedAt')
                ->with($this->isInstanceof('DateTimeImmutable'))
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('withHeader')
                ->with('kid', 'TheKey')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('getToken')
                ->with($this->isInstanceOf('Lcobucci\JWT\Signer\Ecdsa\Sha256'), $key)
                ->willReturn($token);

        $this->logger->expects($this->exactly(2))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with key.' ],
                     );

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $method = $this->get_accessible_reflection_method('httpInit');

        try {
            $method->invoke($this->class);
        } catch (Exception $e) {
            $this->unmock_function('curl_setopt_array');
            $this->unmock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ]);
            $this->unmock_method([ 'Lcobucci\JWT\Configuration', 'builder' ]);

            throw $e;
        }
    }
}
