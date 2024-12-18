<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

interface WebhookMessageFactoryInterface
{
    public function createMessage(WebhookInterface $webhook): HttpRequestMessageInterface;
}
