<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

interface SolariumFactoryInterface
{
    public function createWithDocument(Document $document): SolariumDocument;

    public function createWithDocumentTranslation(DocumentTranslation $documentTranslation): SolariumDocumentTranslation;

    public function createWithNodesSources(NodesSources $nodeSource): SolariumNodeSource;
}
