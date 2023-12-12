<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class SearchRealmNodeInheritanceMessage implements AsyncMessage
{
    private int|string|null $nodeId;

    public function __construct(int|string|null $nodeId)
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return int|string|null
     */
    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }
}
