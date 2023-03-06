<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Document;

use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Contracts\EventDispatcher\Event;

final class DocumentTranslationIndexingEvent extends Event
{
    protected DocumentTranslation $documentTranslation;
    protected array $associations;
    protected AbstractSolarium $solariumDocument;
    protected bool $subResource;

    /**
     * @param DocumentTranslation $documentTranslation
     * @param array $associations
     * @param AbstractSolarium $solariumDocument
     * @param bool $subResource
     */
    public function __construct(
        DocumentTranslation $documentTranslation,
        array $associations,
        AbstractSolarium $solariumDocument,
        bool $subResource = false
    ) {
        $this->documentTranslation = $documentTranslation;
        $this->associations = $associations;
        $this->solariumDocument = $solariumDocument;
        $this->subResource = $subResource;
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

    /**
     * @param AbstractSolarium $solariumDocument
     * @return DocumentTranslationIndexingEvent
     */
    public function setSolariumDocument(AbstractSolarium $solariumDocument): DocumentTranslationIndexingEvent
    {
        $this->solariumDocument = $solariumDocument;
        return $this;
    }
}
