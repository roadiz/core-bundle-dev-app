<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

interface WebhookMessage
{
    /**
     * @param WebhookInterface $webhook
     * @return static
     */
    public static function fromWebhook(WebhookInterface $webhook);
}
