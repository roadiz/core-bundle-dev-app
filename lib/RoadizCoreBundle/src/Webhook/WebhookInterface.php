<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook;

use RZ\Roadiz\Core\AbstractEntities\DateTimedInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface WebhookInterface extends PersistableInterface, DateTimedInterface
{
    public function __toString(): string;

    public function getUri(): ?string;

    public function getMessageType(): ?string;

    public function getPayload(): ?array;

    public function getThrottleSeconds(): int;

    public function doNotTriggerBefore(): ?\DateTime;

    public function setLastTriggeredAt(?\DateTime $lastTriggeredAt): self;

    public function getLastTriggeredAt(): ?\DateTime;

    public function isAutomatic(): bool;
}
