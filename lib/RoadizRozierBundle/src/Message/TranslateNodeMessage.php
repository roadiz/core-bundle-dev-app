<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

final readonly class TranslateNodeMessage implements AsyncMessage
{
    public function __construct(
        private int|string|null $nodeId,
        private int|string|null $sourceTranslationId,
        private int|string|null $destinationTranslationId,
        private bool $translateChildren = false,
    ) {
    }

    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }

    public function getSourceTranslationId(): int|string|null
    {
        return $this->sourceTranslationId;
    }

    public function getDestinationTranslationId(): int|string|null
    {
        return $this->destinationTranslationId;
    }

    public function isTranslateChildren(): bool
    {
        return $this->translateChildren;
    }
}
