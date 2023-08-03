<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class PurgeReverseProxyCacheMessage implements AsyncMessage
{
    private int|string|null $nodeSourceId;

    /**
     * @param int|string|null $nodeSourceId
     */
    public function __construct(int|string|null $nodeSourceId)
    {
        $this->nodeSourceId = $nodeSourceId;
    }

    /**
     * @return int|string|null
     */
    public function getNodeSourceId(): int|string|null
    {
        return $this->nodeSourceId;
    }
}
