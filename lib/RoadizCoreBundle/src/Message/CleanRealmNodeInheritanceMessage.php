<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class CleanRealmNodeInheritanceMessage implements AsyncMessage
{
    public function __construct(
        private readonly int|string|null $nodeId,
        private readonly int|string|null $realmId,
    ) {
    }

    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }

    public function getRealmId(): int|string|null
    {
        return $this->realmId;
    }
}
