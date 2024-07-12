<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Document;

use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Documents\Events\FilterDocumentEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentTranslationUpdatedEvent extends FilterDocumentEvent
{
    public function __construct(
        DocumentInterface $document,
        private readonly ?DocumentTranslation $documentTranslation = null
    ) {
        parent::__construct($document);
    }

    /**
     * @return DocumentTranslation|null
     */
    public function getDocumentTranslation(): ?DocumentTranslation
    {
        return $this->documentTranslation;
    }
}
