<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AttributeValueIndexingSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => 'onNodeSourceIndexing',
        ];
    }

    public function onNodeSourceIndexing(NodesSourcesIndexingEvent $event): void
    {
        if ($event->getNodeSource()->getNode()->getAttributeValues()->count() === 0) {
            return;
        }

        $associations = $event->getAssociations();
        $attributeValues = $event->getNodeSource()
                                ->getNode()
                                ->getAttributesValuesForTranslation($event->getNodeSource()->getTranslation());

        /** @var AttributeValueInterface $attributeValue */
        foreach ($attributeValues as $attributeValue) {
            if ($attributeValue->getAttribute()->isSearchable()) {
                $data = $attributeValue->getAttributeValueTranslation(
                    $event->getNodeSource()->getTranslation()
                )->getValue();
                if (null === $data) {
                    $data = $attributeValue->getAttributeValueTranslations()->first() ?
                        $attributeValue->getAttributeValueTranslations()->first()->getValue()
                        : null;
                }
                if (null !== $data) {
                    switch ($attributeValue->getType()) {
                        case AttributeInterface::DATETIME_T:
                        case AttributeInterface::DATE_T:
                            if ($data instanceof \DateTime) {
                                $fieldName = $attributeValue->getAttribute()->getCode() . '_dt';
                                $associations[$fieldName] = $data->format('Y-m-d\TH:i:s');
                            }
                            break;
                        case AttributeInterface::STRING_T:
                            $fieldName = $attributeValue->getAttribute()->getCode();
                            /*
                            * Use locale to create field name
                            * with right language
                            */
                            if (
                                in_array(
                                    $event->getNodeSource()->getTranslation()->getLocale(),
                                    AbstractSolarium::$availableLocalizedTextFields
                                )
                            ) {
                                $lang = $event->getNodeSource()->getTranslation()->getLocale();
                                $fieldName .= '_txt_' . $lang;
                            } else {
                                $lang = null;
                                $fieldName .= '_t';
                            }
                            /*
                             * Strip Markdown syntax
                             */
                            $content = $event->getSolariumDocument()->cleanTextContent($data);
                            $associations[$fieldName] = $content;
                            $associations['collection_txt'][] = $content;
                            if (null !== $lang) {
                                // Compile all text content into a single localized text field.
                                $associations['collection_txt_' . $lang] = implode(PHP_EOL, $associations['collection_txt']);
                            }
                            break;
                    }
                }
            }
        }

        $event->setAssociations($associations);
    }
}
