<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

/*
 * UpdateNodeTypeSchemaMessage must be handled synchronous
 */
final class UpdateNodeTypeSchemaMessage
{
    private int $nodeTypeId;

    /**
     * @param int $nodeTypeId
     */
    public function __construct(int $nodeTypeId)
    {
        $this->nodeTypeId = $nodeTypeId;
    }

    /**
     * @return int
     */
    public function getNodeTypeId(): int
    {
        return $this->nodeTypeId;
    }
}
