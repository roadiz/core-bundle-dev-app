<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Exception;

final class TooManyWebhookTriggeredException extends \RuntimeException
{
    public function __construct(
        private readonly ?\DateTimeImmutable $doNotTriggerBefore = null,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getDoNotTriggerBefore(): \DateTimeImmutable
    {
        return $this->doNotTriggerBefore ?? \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('PT30S')));
    }
}
