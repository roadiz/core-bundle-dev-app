<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final readonly class PurgeReverseProxyCacheMessage implements AsyncMessage
{
    public function __construct(private int|string|null $nodeSourceId)
    {
    }

    public function getNodeSourceId(): int|string|null
    {
        return $this->nodeSourceId;
    }
}
