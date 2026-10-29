<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Webhook\Message;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\Webhook\Message\GenericJsonPostMessageInterface;

/**
 * Outbound webhook payloads must carry an HMAC signature header when a
 * secret is configured, so receivers can verify the payload actually came
 * from this Roadiz instance.
 */
final class GenericJsonPostMessageInterfaceTest extends TestCase
{
    public function testSignatureHeaderIsAddedWhenSecretIsSet(): void
    {
        $webhook = (new Webhook())
            ->setUri('https://example.test/hook')
            ->setPayload(['foo' => 'bar'])
            ->setSecret('s3cr3t');

        $message = GenericJsonPostMessageInterface::fromWebhook($webhook);
        $options = $message->getOptions();

        $expected = 'sha256='.hash_hmac('sha256', $options['body'], 's3cr3t');
        $this->assertSame($expected, $options['headers']['X-Roadiz-Signature']);
    }

    public function testSignatureHeaderIsAbsentWithoutSecret(): void
    {
        $webhook = (new Webhook())
            ->setUri('https://example.test/hook')
            ->setPayload(['foo' => 'bar']);

        $message = GenericJsonPostMessageInterface::fromWebhook($webhook);
        $options = $message->getOptions();

        $this->assertArrayNotHasKey('X-Roadiz-Signature', $options['headers']);
    }
}
