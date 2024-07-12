<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class SearchRealmNodeInheritanceMessage implements AsyncMessage
{
    public function __construct(private readonly int|string|null $nodeId)
    {
    }

    /**
     * @return int|string|null
     */
    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }
}
