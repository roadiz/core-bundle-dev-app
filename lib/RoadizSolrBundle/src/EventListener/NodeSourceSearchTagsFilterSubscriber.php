<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use Solarium\Core\Query\Helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Restrict node-source search results to the tags selected through the
 * `tag_name` request query param, matching the `facet_tags_ss` field
 * exposed by NodeSourceSearchFacetSubscriber.
 */
final readonly class NodeSourceSearchTagsFilterSubscriber implements EventSubscriberInterface
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

        $tagsNames = $request->query->all()['tag_name'] ?? null;
        if (is_string($tagsNames)) {
            $tagsNames = [$tagsNames];
        }
        if (!is_array($tagsNames)) {
            return;
        }

        $helper = new Helper();
        $phrases = [];
        foreach ($tagsNames as $tagName) {
            if (!is_string($tagName) || '' === trim($tagName)) {
                continue;
            }
            $phrases[] = 'facet_tags_ss:'.$helper->escapePhrase($tagName);
        }

        if (0 === count($phrases)) {
            return;
        }

        $event->getQuery()->createFilterQuery('tag_name')
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
