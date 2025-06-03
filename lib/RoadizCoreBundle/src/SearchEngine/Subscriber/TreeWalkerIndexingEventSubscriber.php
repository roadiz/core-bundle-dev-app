<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumFactoryInterface;
use RZ\TreeWalker\WalkerContextInterface;
use RZ\TreeWalker\WalkerInterface;

/**
 * Index sub nodes content into any reachable node-source using AutoChildrenNodeSourceWalker.
 */
final class TreeWalkerIndexingEventSubscriber extends AbstractIndexingSubscriber
{
    public function __construct(
        private readonly WalkerContextInterface $walkerContext,
        private readonly SolariumFactoryInterface $solariumFactory,
        private readonly int $maxLevel = 5,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => ['onIndexing', -99],
        ];
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        $nodeSource = $event->getNodeSource();
        if (!$nodeSource->isReachable() || $event->isSubResource()) {
            return;
        }

        $assoc = $event->getAssociations();

        $blockWalker = AutoChildrenNodeSourceWalker::build(
            $nodeSource,
            $this->walkerContext,
            $this->maxLevel
        );

        // Need a locale field
        $locale = $nodeSource->getTranslation()->getLocale();
        $lang = \Locale::getPrimaryLanguage($locale) ?? $this->defaultLocale;

        try {
            foreach ($blockWalker->getChildren() as $subWalker) {
                $this->walkAndIndex($subWalker, $assoc, $lang);
            }
        } catch (\Exception) {
        }

        $event->setAssociations($assoc);
    }

    /**
     * @throws \Exception
     */
    protected function walkAndIndex(WalkerInterface $walker, array &$assoc, string $locale): void
    {
        $item = $walker->getItem();
        if ($item instanceof NodesSources) {
            $solarium = $this->solariumFactory->createWithNodesSources($item);
            // Fetch all fields array association AS sub-resources (i.e. do not index their title, and relationships)
            $childAssoc = $solarium->getFieldsAssoc(true);
            $assoc['collection_txt'] = array_filter(array_merge(
                $assoc['collection_txt'],
                $childAssoc['collection_txt']
            ));
            $assoc['collection_txt_'.$locale] = $this->flattenTextCollection($assoc['collection_txt']);
        }
        if ($walker->count() > 0) {
            foreach ($walker->getChildren() as $subWalker) {
                $this->walkAndIndex($subWalker, $assoc, $locale);
            }
        }
    }
}
