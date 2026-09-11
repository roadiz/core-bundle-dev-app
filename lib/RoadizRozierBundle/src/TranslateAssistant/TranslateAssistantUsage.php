<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

final readonly class TranslateAssistantUsage
{
    public function __construct(
        public int $characterCount,
        public int $characterLimit,
    ) {
    }

    public function getPercentage(): float
    {
        if ($this->characterLimit <= 0) {
            return 0.0;
        }

        return min(100.0, $this->characterCount / $this->characterLimit * 100);
    }

    public function isLimitReached(): bool
    {
        return $this->characterCount >= $this->characterLimit;
    }
}
