<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NodeSourceSearchFacetSubscriber implements EventSubscriberInterface
{
    public function onQuery(NodeSourceSearchQueryEvent $event): void
    {
        $facetSet = $event->getQuery()->getFacetSet();
        $facetSet->createJsonFacetTerms(['local_key' => 'node_type', 'field' => 'node_type_s', 'local_exclude' => 'node_type']);
        $facetSet->createJsonFacetTerms(['local_key' => 'document_type', 'field' => 'document_type_s', 'local_exclude' => 'document_type']);
        $facetSet->createJsonFacetTerms(['local_key' => 'tag_name', 'field' => 'facet_tags_ss', 'local_exclude' => 'tag_name']);
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodeSourceSearchQueryEvent::class => 'onQuery',
        ];
    }
}
