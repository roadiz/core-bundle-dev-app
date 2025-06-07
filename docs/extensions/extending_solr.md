---
subtitle: How to index page blocks contents
title: Extending Solr indexation
---

# Extending Solr

If all your text content is written in *block nodes* instead of reachable *pages*, you should index them into your page Solr documents to improve your search engine relevancy.

You can use the `NodesSourcesIndexingEvent::class` event to enhance your node indexing data before it's persisted into *Solr* engine (especially `collection_txt` field):

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;use RZ\Roadiz\CoreBundle\Entity\NodesSources;use RZ\Roadiz\CoreBundle\SearchEngine\SolariumFactoryInterface;use RZ\Roadiz\SolrBundle\Event\NodesSources\NodesSourcesIndexingEvent;use RZ\TreeWalker\WalkerContextInterface;use RZ\TreeWalker\WalkerInterface;use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Index sub nodes content into any reachable node-source.
 */
final class NodeSourceIndexingEventSubscriber implements EventSubscriberInterface
{
    private WalkerContextInterface $walkerContext;
    private SolariumFactoryInterface $solariumFactory;
    private int $maxLevel;

    /**
     * @param WalkerContextInterface $walkerContext
     * @param SolariumFactoryInterface $solariumFactory
     * @param int $maxLevel
     */
    public function __construct(
        WalkerContextInterface $walkerContext,
        SolariumFactoryInterface $solariumFactory,
        int $maxLevel = 5
    ) {
        $this->walkerContext = $walkerContext;
        $this->solariumFactory = $solariumFactory;
        $this->maxLevel = $maxLevel;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => ['onIndexing'],
        ];
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        $nodeSource = $event->getNodeSource();

        if (null !== $nodeSource->getNode() && $nodeSource->isReachable() && !$event->isSubResource()) {
            $assoc = $event->getAssociations();

            $blockWalker = AutoChildrenNodeSourceWalker::build(
                $nodeSource,
                $this->walkerContext,
                $this->maxLevel
            );

            // Need a locale field
            $locale = $nodeSource->getTranslation()->getLocale();
            $lang = \Locale::getPrimaryLanguage($locale) ?? 'fr';

            foreach ($blockWalker->getChildren() as $subWalker) {
                $this->walkAndIndex($subWalker, $assoc, $lang);
            }

            $event->setAssociations($assoc);
        }
    }

    /**
     * @param WalkerInterface $walker
     * @param array $assoc
     * @param string $locale
     * @throws \Exception
     */
    protected function walkAndIndex(WalkerInterface $walker, array &$assoc, string $locale): void
    {
        $item = $walker->getItem();
        if ($item instanceof NodesSources) {
            $solarium = $this->solariumFactory->createWithNodesSources($walker->getItem());
            // Fetch all fields array association AS sub-resources (i.e. do not index their title)
            $childAssoc = $solarium->getFieldsAssoc(true);
            $assoc['collection_txt'] = array_filter(array_merge(
                $assoc['collection_txt'],
                $childAssoc['collection_txt']
            ));
            if (!empty($childAssoc['collection_txt_' . $locale])) {
                $assoc['collection_txt_' . $locale] .= PHP_EOL . $childAssoc['collection_txt_' . $locale];
            }
        }
        if ($walker->count() > 0) {
            foreach ($walker->getChildren() as $subWalker) {
                $this->walkAndIndex($subWalker, $assoc, $locale);
            }
        }
    }
}
```
