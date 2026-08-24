<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Message\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Message\Handler\HttpRequestMessageHandler;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Covers security audit finding M1: the webhook handler must be wired to a
 * private-network-guarded HTTP client (services.yaml), same as media finders,
 * so an admin-set webhook URI cannot be used as an SSRF pivot.
 */
final class HttpRequestMessageHandlerTest extends TestCase
{
    public function testRequestToPrivateNetworkUriIsBlocked(): void
    {
        $client = new NoPrivateNetworkHttpClient(new MockHttpClient());
        $handler = new HttpRequestMessageHandler($client, new NullLogger());

        $this->expectException(TransportExceptionInterface::class);

        $handler(new HttpRequestMessage('GET', 'http://169.254.169.254/latest/meta-data'));
    }
}
