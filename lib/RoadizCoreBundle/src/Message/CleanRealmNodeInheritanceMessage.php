<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class CleanRealmNodeInheritanceMessage implements AsyncMessage
{
    private int $nodeId;
    private ?int $realmId;

    public function __construct(int $nodeId, ?int $realmId)
    {
        $this->nodeId = $nodeId;
        $this->realmId = $realmId;
    }

    /**
     * @return int
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }

    /**
     * @return int|null
     */
    public function getRealmId(): ?int
    {
        return $this->realmId;
    }
}
