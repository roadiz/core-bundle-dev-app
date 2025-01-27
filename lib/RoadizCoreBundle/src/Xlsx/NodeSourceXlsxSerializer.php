<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Xlsx;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated XLSX serialization is deprecated and will be removed in next major version.
 * XLSX Serialization handler for NodeSource.
 */
final class NodeSourceXlsxSerializer extends AbstractXlsxSerializer
{
    protected bool $addUrls = false;
    protected bool $onlyTexts = false;

    public function __construct(
        TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly NodeTypes $nodeTypesBag,
    ) {
        parent::__construct($translator);
    }

    /**
     * Create a simple associative array with a NodeSource.
     *
     * @param NodesSources|iterable<NodesSources>|null $nodeSource
     */
    public function toArray(mixed $nodeSource): array
    {
        $data = [];

        if ($nodeSource instanceof NodesSources) {
            if (true === $this->addUrls) {
                $data['_url'] = $this->urlGenerator->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }

            $data['translation'] = $nodeSource->getTranslation()->getLocale();
            $data['title'] = $nodeSource->getTitle();
            $data['published_at'] = $nodeSource->getPublishedAt();
            $data['meta_title'] = $nodeSource->getMetaTitle();
            $data['meta_description'] = $nodeSource->getMetaDescription();

            $data = array_merge($data, $this->getSourceFields($nodeSource));
        } elseif (is_iterable($nodeSource)) {
            /*
             * If asked to serialize a nodeSource collection
             */
            foreach ($nodeSource as $singleSource) {
                $data[] = $this->toArray($singleSource);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSourceFields(NodesSources $nodeSource): array
    {
        $fields = $this->getFields($this->nodeTypesBag->get($nodeSource->getNode()->getNodeTypeName()));

        /*
         * Create nodeSource default values
         */
        $sourceDefaults = [];
        foreach ($fields as $field) {
            $getter = $field->getGetterName();
            $sourceDefaults[$field->getName()] = $nodeSource->$getter();
        }

        return $sourceDefaults;
    }

    /**
     * @return NodeTypeField[]
     */
    protected function getFields(NodeTypeInterface $nodeType): array
    {
        if (true === $this->onlyTexts) {
            $types = [
                AbstractField::STRING_T,
                AbstractField::TEXT_T,
                AbstractField::MARKDOWN_T,
                AbstractField::RICHTEXT_T,
            ];
        } else {
            $types = [
                AbstractField::STRING_T,
                AbstractField::DATETIME_T,
                AbstractField::DATE_T,
                AbstractField::RICHTEXT_T,
                AbstractField::TEXT_T,
                AbstractField::MARKDOWN_T,
                AbstractField::BOOLEAN_T,
                AbstractField::INTEGER_T,
                AbstractField::DECIMAL_T,
                AbstractField::EMAIL_T,
                AbstractField::ENUM_T,
                AbstractField::MULTIPLE_T,
                AbstractField::COLOUR_T,
                AbstractField::GEOTAG_T,
                AbstractField::MULTI_GEOTAG_T,
            ];
        }

        return $nodeType->getFields()->filter(function (NodeTypeField $field) use ($types) {
            return in_array($field->getType(), $types);
        })->toArray();
    }

    public function deserialize(string $string): null
    {
        return null;
    }

    /**
     * Serialize only texts.
     */
    public function setOnlyTexts(bool $onlyTexts = true): self
    {
        $this->onlyTexts = $onlyTexts;

        return $this;
    }

    public function addUrls(): self
    {
        $this->addUrls = true;

        return $this;
    }
}
