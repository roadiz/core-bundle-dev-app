<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use Solarium\Core\Query\Helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Narrow node-source search results to the node types selected through the
 * `node_type` request query param, matching the `node_type_s` field exposed by
 * NodeSourceSearchFacetSubscriber.
 *
 * This only ever restricts within the node-type allowlist already enforced by
 * NodesSourcesSearchController::getCriteria(): the two filter queries are ANDed
 * together, so requesting a type outside the allowlist simply yields no results.
 */
final readonly class NodeSourceSearchNodeTypeFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function onQuery(NodeSourceSearchQueryEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $nodeTypes = $request->query->all()['node_type'] ?? null;
        if (is_string($nodeTypes)) {
            $nodeTypes = [$nodeTypes];
        }
        if (!is_array($nodeTypes)) {
            return;
        }

        $helper = new Helper();
        $phrases = [];
        foreach ($nodeTypes as $nodeType) {
            if (!is_string($nodeType) || '' === trim($nodeType)) {
                continue;
            }
            $phrases[] = 'node_type_s:'.$helper->escapePhrase($nodeType);
        }

        if (0 === count($phrases)) {
            return;
        }

        $event->getQuery()->createFilterQuery('node_type')
            ->setQuery(implode(' OR ', $phrases));
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodeSourceSearchQueryEvent::class => 'onQuery',
        ];
    }
}
