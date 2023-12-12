<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class DeleteNodeTypeMessage implements AsyncMessage
{
    private int|string|null $nodeTypeId;

    /**
     * @param int|string|null $nodeTypeId
     */
    public function __construct(int|string|null $nodeTypeId)
    {
        $this->nodeTypeId = $nodeTypeId;
    }

    /**
     * @return int|string|null
     */
    public function getNodeTypeId(): int|string|null
    {
        return $this->nodeTypeId;
    }
}
