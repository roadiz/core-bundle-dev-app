<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook;

interface WebhookDispatcher
{
    public function dispatch(WebhookInterface $webhook): void;
}
