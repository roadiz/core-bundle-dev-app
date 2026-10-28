<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

/**
 * What a subtree translation would cost, against what the provider quota still allows.
 */
final readonly class TranslateAssistantEstimate
{
    public function __construct(
        public int $characterCount,
        public int $sourceCount,
        /**
         * Null when the provider exposes no quota, or when its quota could not be read.
         */
        public ?int $charactersRemaining,
    ) {
    }

    public function fitsInQuota(): bool
    {
        return null === $this->charactersRemaining || $this->characterCount <= $this->charactersRemaining;
    }

    public function isEmpty(): bool
    {
        return 0 === $this->sourceCount;
    }
}
