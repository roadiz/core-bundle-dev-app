<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class AttributeValueIndexingSubscriber extends AbstractIndexingSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesIndexingEvent::class => ['onIndexing', 900],
        ];
    }

    public function onIndexing(NodesSourcesIndexingEvent $event): void
    {
        if ($event->isSubResource()) {
            return;
        }

        $associations = $event->getAssociations();
        $attributeValues = $event->getNodeSource()
                                ->getNode()
                                ->getAttributesValuesForTranslation($event->getNodeSource()->getTranslation());

        if (0 === $attributeValues->count()) {
            return;
        }

        $lang = $event->getNodeSource()->getTranslation()->getLocale();
        if (
            !\in_array(
                $lang,
                AbstractSolarium::$availableLocalizedTextFields
            )
        ) {
            $lang = null;
        }

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
                    $fieldName = (new AsciiSlugger())->slug($attributeValue->getAttribute()->getCode())->snake()->lower()->toString();
                    switch ($attributeValue->getType()) {
                        case AttributeInterface::INTEGER_T:
                            $fieldName .= '_i';
                            $associations[$fieldName] = $data;
                            break;
                        case AttributeInterface::DECIMAL_T:
                        case AttributeInterface::PERCENT_T:
                            $fieldName .= '_f';
                            $associations[$fieldName] = $data;
                            break;
                        case AttributeInterface::ENUM_T:
                        case AttributeInterface::COUNTRY_T:
                        case AttributeInterface::COLOUR_T:
                        case AttributeInterface::EMAIL_T:
                            $fieldName .= '_s';
                            $content = $event->getSolariumDocument()->cleanTextContent($data);
                            $associations[$fieldName] = $content;
                            $associations['collection_txt'][] = $content;
                            if (null !== $lang) {
                                // Compile all text content into a single localized text field.
                                $associations['collection_txt_'.$lang] = $this->flattenTextCollection($associations['collection_txt']);
                            }
                            break;
                        case AttributeInterface::DATETIME_T:
                        case AttributeInterface::DATE_T:
                            if ($data instanceof \DateTimeInterface) {
                                $fieldName .= '_dt';
                                $associations[$fieldName] = $this->formatDateTimeToUTC($data);
                            }
                            break;
                        case AttributeInterface::STRING_T:
                            /*
                            * Use locale to create field name
                            * with right language
                            */
                            if (null !== $lang) {
                                $fieldName .= '_txt_'.$lang;
                            } else {
                                $lang = null;
                                $fieldName .= '_t';
                            }
                            /*
                             * Strip Markdown syntax
                             */
                            $content = $event->getSolariumDocument()->cleanTextContent($data);
                            if (null !== $content) {
                                $content = trim($content);
                                $associations[$fieldName] = $content;
                                $associations['collection_txt'][] = $content;
                                if (null !== $lang) {
                                    // Compile all text content into a single localized text field.
                                    $associations['collection_txt_'.$lang] = $this->flattenTextCollection($associations['collection_txt']);
                                }
                            }
                            break;
                    }
                }
            }
        }

        $event->setAssociations($associations);
    }
}
