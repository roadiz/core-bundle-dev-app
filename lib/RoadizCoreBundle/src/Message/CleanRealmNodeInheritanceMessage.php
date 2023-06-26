<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class CleanRealmNodeInheritanceMessage implements AsyncMessage
{
    private int|string|null $nodeId;
    private int|string|null $realmId;

    public function __construct(int|string|null $nodeId, int|string|null $realmId)
    {
        $this->nodeId = $nodeId;
        $this->realmId = $realmId;
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
