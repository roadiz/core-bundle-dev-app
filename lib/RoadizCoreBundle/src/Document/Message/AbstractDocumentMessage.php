<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

abstract class AbstractDocumentMessage implements AsyncMessage
{
    public function __construct(private readonly int $documentId)
    {
    }

    public function getDocumentId(): int
    {
        return $this->documentId;
    }
}
