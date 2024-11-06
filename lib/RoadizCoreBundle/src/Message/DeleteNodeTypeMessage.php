<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class DeleteNodeTypeMessage implements AsyncMessage
{
    public function __construct(private readonly int|string|null $nodeTypeId)
    {
    }

    public function getNodeTypeId(): int|string|null
    {
        return $this->nodeTypeId;
    }
}
