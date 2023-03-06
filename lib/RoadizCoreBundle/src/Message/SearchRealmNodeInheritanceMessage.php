<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class SearchRealmNodeInheritanceMessage implements AsyncMessage
{
    private int $nodeId;

    public function __construct(int $nodeId)
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return int
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }
}
