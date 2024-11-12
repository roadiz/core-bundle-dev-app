<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final class PurgeReverseProxyCacheMessage implements AsyncMessage
{
    public function __construct(private readonly int|string|null $nodeSourceId)
    {
    }

    public function getNodeSourceId(): int|string|null
    {
        return $this->nodeSourceId;
    }
}
