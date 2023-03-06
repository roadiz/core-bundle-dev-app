<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

abstract class AbstractDocumentMessage implements AsyncMessage
{
    private int $documentId;

    /**
     * @param int $documentId
     */
    public function __construct(int $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * @return int
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }
}
