<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\QueryType\Update\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Wrap a Solarium and a NodeSource together to ease indexing.
 */
class SolariumNodeSource extends AbstractSolarium
{
    public const DOCUMENT_TYPE = 'NodesSources';
    public const IDENTIFIER_KEY = 'node_source_id_i';

    public function __construct(
        protected readonly NodesSources $nodeSource,
        ClientRegistry $clientRegistry,
        protected readonly EventDispatcherInterface $dispatcher,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown,
    ) {
        parent::__construct($clientRegistry, $searchEngineLogger, $markdown);
    }

    public function getDocumentId(): int|string
    {
        return $this->nodeSource->getId() ?? throw new \RuntimeException('NodeSource must have an ID');
    }

    /**
     * Get a key/value array representation of current node-source document.
     *
     * @param bool $subResource Tell when this field gathering is for a main resource indexation or a sub-resource
     *
     * @throws \Exception
     */
    public function getFieldsAssoc(bool $subResource = false): array
    {
        $event = new NodesSourcesIndexingEvent($this->nodeSource, [], $this);
        /** @var NodesSourcesIndexingEvent $event */
        $event = $this->dispatcher->dispatch($event);

        return $event->getAssociations();
    }

    /**
     * Remove any document linked to current node-source.
     */
    public function clean(Query $update): bool
    {
        $update->addDeleteQuery(
            static::IDENTIFIER_KEY.':"'.$this->nodeSource->getId().'"'.
            '&'.static::TYPE_DISCRIMINATOR.':"'.static::DOCUMENT_TYPE.'"'.
            '&locale_s:"'.$this->nodeSource->getTranslation()->getLocale().'"'
        );

        return true;
    }

    protected function getIdempotentIdentifier(): string
    {
        $namespace = explode('\\', get_class($this->nodeSource));
        // get last 3 parts of namespace
        $namespace = array_slice($namespace, -3);

        return (new AsciiSlugger())->slug(implode(' ', $namespace))->lower()->snake().'.'.$this->nodeSource->getId();
    }
}
