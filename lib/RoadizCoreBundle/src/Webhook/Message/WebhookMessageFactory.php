<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final class WebhookMessageFactory implements WebhookMessageFactoryInterface
{
    public function createMessage(WebhookInterface $webhook): HttpRequestMessage
    {
        if (null === $webhook->getMessageType()) {
            throw new \LogicException('Webhook message type is null.');
        }

        /** @var class-string $messageType */
        $messageType = $webhook->getMessageType();

        if (!class_exists($messageType)) {
            throw new \LogicException('Webhook message type does not exist.');
        }
        if (!in_array(WebhookMessage::class, class_implements($messageType))) {
            throw new \LogicException('Webhook message type does not implement ' . WebhookMessage::class);
        }

        return $messageType::fromWebhook($webhook);
    }
}
