<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumNodeSource;

final class DefaultNodesSourcesIndexingSubscriber extends AbstractIndexingSubscriber
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => ['onIndexing', 1000],
        ];
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        $nodeSource = $event->getNodeSource();
        $subResource = $event->isSubResource();
        $assoc = $event->getAssociations();
        $collection = [];
        $node = $nodeSource->getNode();

        // Need a documentType field
        $assoc[AbstractSolarium::TYPE_DISCRIMINATOR] = SolariumNodeSource::DOCUMENT_TYPE;
        // Need a nodeSourceId field
        $assoc[SolariumNodeSource::IDENTIFIER_KEY] = $nodeSource->getId();

        // Need a locale field
        $locale = $nodeSource->getTranslation()->getLocale();
        $lang = \Locale::getPrimaryLanguage($locale);
        $assoc['locale_s'] = $locale;

        /*
         * Index resource title
         */
        $title = $event->getSolariumDocument()->cleanTextContent($nodeSource->getTitle(), false);
        $assoc['title'] = $title;
        $assoc['title_txt_' . $lang] = $title;

        /*
         * Do not index locale and tags if this is a sub-resource
         */
        if (!$subResource) {
            $assoc['node_type_s'] = $nodeSource->getNodeTypeName();
            $assoc['node_name_s'] = $node->getNodeName();
            $assoc['slug_s'] = $node->getNodeName();
            $assoc['node_status_i'] = $node->getStatus();
            $assoc['node_visible_b'] = $node->isVisible();
            $assoc['node_reachable_b'] = $nodeSource->isReachable();
            $assoc['created_at_dt'] = $this->formatDateTimeToUTC($node->getCreatedAt());
            $assoc['updated_at_dt'] = $this->formatDateTimeToUTC($node->getUpdatedAt());

            if (null !== $nodeSource->getPublishedAt()) {
                $assoc['published_at_dt'] =  $this->formatDateTimeToUTC($nodeSource->getPublishedAt());
            }

            if ($this->canIndexTitleInCollection($nodeSource)) {
                $collection[] = $title;
            }
            /*
             * Index parent node ID and name to filter on it
             */
            $parent = $node->getParent();
            if (null !== $parent) {
                $assoc['node_parent_i'] = $parent->getId();
                $assoc['node_parent_s'] = $parent->getNodeName();
            }

            /*
             * `tags_txt` Must store only public, visible and user-searchable content.
             */
            $out = array_map(
                function (Tag $tag) use ($event, $nodeSource) {
                    $translatedTag = $tag->getTranslatedTagsByTranslation($nodeSource->getTranslation())->first();
                    $tagName = $translatedTag ?
                        $translatedTag->getName() :
                        $tag->getTagName();
                    return $event->getSolariumDocument()->cleanTextContent($tagName, false);
                },
                $nodeSource->getNode()->getTags()->filter(function (Tag $tag) {
                    return $tag->isVisible();
                })->toArray()
            );
            $out = array_filter(array_unique($out));
            // Use tags_txt to be compatible with other data types
            $assoc['tags_txt'] = $out;
            // Compile all tags names into a single localized text field.
            $assoc['tags_txt_' . $lang] = implode(' ', $out);

            /*
             * `all_tags_slugs_ss` can store all tags, even technical one, this fields should not user searchable.
             */
            $allOut = array_map(
                function (Tag $tag) {
                    return $tag->getTagName();
                },
                $nodeSource->getNode()->getTags()->toArray()
            );
            $allOut = array_filter(array_unique($allOut));
            // Use all_tags_slugs_ss to be compatible with other data types
            $assoc['all_tags_slugs_ss'] = $allOut;

            $booleanFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isBoolean();
            });
            $this->indexSuffixedFields($booleanFields, '_b', $nodeSource, $assoc);

            $numberFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isInteger();
            });
            $this->indexSuffixedFields($numberFields, '_i', $nodeSource, $assoc);

            $decimalFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isDecimal();
            });
            $this->indexSuffixedFields($decimalFields, '_f', $nodeSource, $assoc);

            $stringFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isEnum() || $field->isCountry() || $field->isColor() || $field->isEmail();
            });
            $this->indexSuffixedFields($stringFields, '_s', $nodeSource, $assoc);

            $dateTimeFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isDate() || $field->isDateTime();
            });
            $this->indexSuffixedFields($dateTimeFields, '_dt', $nodeSource, $assoc);

            /*
             * Make sure your Solr managed-schema has a field named `*_p` with type `location` singleValued
             * <dynamicField name="*_p" type="location" indexed="true" stored="true" multiValued="false"/>
             */
            $pointFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isGeoTag();
            });
            foreach ($pointFields as $field) {
                $name = $field->getName();
                $name .= '_p';
                $getter = $field->getGetterName();
                $value = $nodeSource->$getter();
                $assoc[$name] = $this->formatGeoJsonFeature($value);
            }

            /*
             * Make sure your Solr managed-schema has a field named `*_ps` with type `location` multiValued
             * <dynamicField name="*_ps" type="location" indexed="true" stored="true" multiValued="true"/>
             */
            $multiPointFields = $node->getNodeType()->getFields()->filter(function (NodeTypeField $field) {
                return $field->isMultiGeoTag();
            });
            foreach ($multiPointFields as $field) {
                $name = $field->getName();
                $name .= '_ps';
                $getter = $field->getGetterName();
                $value = $nodeSource->$getter();
                $assoc[$name] = $this->formatGeoJsonFeatureCollection($value);
            }
        }

        $searchableFields = $node->getNodeType()->getSearchableFields();
        /** @var NodeTypeField $field */
        foreach ($searchableFields as $field) {
            $name = $field->getName();
            $getter = $field->getGetterName();
            $content = $nodeSource->$getter();
            /*
             * Strip markdown syntax
             */
            $content = $event->getSolariumDocument()->cleanTextContent($content);
            /*
             * Use locale to create field name
             * with right language
             */
            if (in_array($lang, SolariumNodeSource::$availableLocalizedTextFields)) {
                $name .= '_txt_' . $lang;
            } else {
                $name .= '_t';
            }

            $assoc[$name] = $content;
            $collection[] = $content;
        }
        /*
         * Collect data in a single field
         * for global search
         */
        $assoc['collection_txt'] = $collection;
        // Compile all text content into a single localized text field.
        $assoc['collection_txt_' . $lang] = $this->flattenTextCollection($collection);
        $event->setAssociations($assoc);
    }

    /**
     * @param iterable<NodeTypeField> $fields
     * @param string $suffix
     * @param NodesSources $nodeSource
     * @param array $assoc
     * @return void
     */
    protected function indexSuffixedFields(iterable $fields, string $suffix, NodesSources $nodeSource, array &$assoc): void
    {
        foreach ($fields as $field) {
            $name = $field->getName();
            $name .= $suffix;
            $getter = $field->getGetterName();
            $value = $nodeSource->$getter();
            if ($value instanceof \DateTimeInterface) {
                $assoc[$name] = $this->formatDateTimeToUTC($value);
            } elseif (null !== $value) {
                $assoc[$name] = $value;
            }
        }
    }

    /**
     * @param NodesSources $source
     * @return bool
     */
    protected function canIndexTitleInCollection(NodesSources $source): bool
    {
        if (method_exists($source, 'getHideTitle')) {
            return !((bool) $source->getHideTitle());
        }
        if (method_exists($source, 'getShowTitle')) {
            return ((bool) $source->getShowTitle());
        }

        if (null !== $source->getNode() && $source->getNode()->getNodeType()) {
            return $source->getNode()->getNodeType()->isSearchable();
        }
        return true;
    }
}
