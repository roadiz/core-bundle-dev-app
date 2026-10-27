<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Node-sources in a realm hiding content (deny or hide_blocks) stay searchable,
 * but only by their title: every other text field is stripped before indexing,
 * so protected content never reaches Solr nor its highlighting.
 */
final class NodeSourceSearchRealmFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RealmResolverInterface $realmResolver,
    ) {
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        if ($event->isSubResource()) {
            return;
        }

        $protectedRealms = array_filter(
            $this->realmResolver->getRealms($event->getNodeSource()->getNode()),
            fn (RealmInterface $realm) => RealmInterface::BEHAVIOUR_NONE !== $realm->getBehaviour()
        );
        if ([] === $protectedRealms) {
            return;
        }

        $assoc = array_filter(
            $event->getAssociations(),
            fn (string $key) => str_starts_with($key, 'title') || !preg_match('/(_txt(_[a-z]+)?|_t)$/', $key),
            ARRAY_FILTER_USE_KEY
        );
        $title = $assoc['title'] ?? '';
        $lang = \Locale::getPrimaryLanguage($event->getNodeSource()->getTranslation()->getLocale());
        $assoc['collection_txt'] = [$title];
        $assoc['collection_txt_'.$lang] = $title;

        $event->setAssociations($assoc);
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            // After every other indexing subscriber, TreeWalker blocks included (-99)
            NodesSourcesIndexingEvent::class => ['onIndexing', -1000],
        ];
    }
}
