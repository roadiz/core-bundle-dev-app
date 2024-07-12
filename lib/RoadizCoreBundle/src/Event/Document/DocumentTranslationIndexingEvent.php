<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Document;

use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Contracts\EventDispatcher\Event;

final class DocumentTranslationIndexingEvent extends Event
{
    public function __construct(
        private readonly DocumentTranslation $documentTranslation,
        private array $associations,
        private readonly AbstractSolarium $solariumDocument,
        private readonly bool $subResource = false
    ) {
    }

    /**
     * @return DocumentTranslation
     */
    public function getDocumentTranslation(): DocumentTranslation
    {
        return $this->documentTranslation;
    }

    /**
     * @return array
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    /**
     * @return AbstractSolarium
     */
    public function getSolariumDocument(): AbstractSolarium
    {
        return $this->solariumDocument;
    }

    /**
     * @return bool
     */
    public function isSubResource(): bool
    {
        return $this->subResource;
    }

    /**
     * @param array $associations
     * @return DocumentTranslationIndexingEvent
     */
    public function setAssociations(array $associations): DocumentTranslationIndexingEvent
    {
        $this->associations = $associations;
        return $this;
    }
}
