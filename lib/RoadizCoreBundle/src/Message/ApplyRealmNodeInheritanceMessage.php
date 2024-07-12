<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class ApplyRealmNodeInheritanceMessage implements AsyncMessage
{
    public function __construct(
        private readonly int|string|null $nodeId,
        private readonly int|string|null $realmId
    ) {
    }

    /**
     * @return int|string|null
     */
    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }

    /**
     * @return int|string|null
     */
    public function getRealmId(): int|string|null
    {
        return $this->realmId;
    }
}
