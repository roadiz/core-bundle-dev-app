<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationIndexingEvent;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\QueryType\Update\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Wrap a Solarium and a DocumentTranslation together to ease indexing.
 */
class SolariumDocumentTranslation extends AbstractSolarium
{
    public const DOCUMENT_TYPE = 'DocumentTranslation';
    public const IDENTIFIER_KEY = 'document_translation_id_i';

    public function __construct(
        protected readonly DocumentTranslation $documentTranslation,
        ClientRegistry $clientRegistry,
        protected readonly EventDispatcherInterface $dispatcher,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown,
    ) {
        parent::__construct($clientRegistry, $searchEngineLogger, $markdown);
    }

    #[\Override]
    public function getDocumentId(): int|string
    {
        return $this->documentTranslation->getId();
    }

    #[\Override]
    public function getFieldsAssoc(bool $subResource = false): array
    {
        $event = new DocumentTranslationIndexingEvent($this->documentTranslation, [], $this);
        /** @var DocumentTranslationIndexingEvent $event */
        $event = $this->dispatcher->dispatch($event);

        return $event->getAssociations();
    }

    /**
     * Remove any document linked to current node-source.
     */
    #[\Override]
    public function clean(Query $update): bool
    {
        $update->addDeleteQuery(
            static::IDENTIFIER_KEY.':"'.$this->documentTranslation->getId().'"'.
            '&'.static::TYPE_DISCRIMINATOR.':"'.static::DOCUMENT_TYPE.'"'.
            '&locale_s:"'.$this->documentTranslation->getTranslation()->getLocale().'"'
        );

        return true;
    }

    #[\Override]
    protected function getIdempotentIdentifier(): string
    {
        $namespace = explode('\\', $this->documentTranslation::class);
        // get last 3 parts of namespace
        $namespace = array_slice($namespace, -3);

        return (new AsciiSlugger())->slug(implode(' ', $namespace))->lower()->snake().'.'.$this->documentTranslation->getId();
    }
}
