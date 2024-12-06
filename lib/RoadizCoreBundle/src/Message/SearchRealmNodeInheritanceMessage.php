<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final readonly class SearchRealmNodeInheritanceMessage implements AsyncMessage
{
    public function __construct(private int|string|null $nodeId)
    {
    }

    public function getNodeId(): int|string|null
    {
        return $this->nodeId;
    }
}
