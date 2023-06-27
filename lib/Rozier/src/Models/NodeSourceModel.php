<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
final class NodeSourceModel implements ModelInterface
{
    private NodesSources $nodeSource;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(NodesSources $nodeSource, UrlGeneratorInterface $urlGenerator)
    {
        $this->nodeSource = $nodeSource;
        $this->urlGenerator = $urlGenerator;
    }

    public function toArray(): array
    {
        $node = $this->nodeSource->getNode();

        /** @var NodesSourcesDocuments|false $thumbnail */
        $thumbnail = $this->nodeSource->getDocumentsByFields()->first();
        /** @var Translation $translation */
        $translation = $this->nodeSource->getTranslation();

        $result = [
            'id' => $node->getId(),
            'title' => $this->nodeSource->getTitle(),
            'nodeName' => $node->getNodeName(),
            'thumbnail' => $thumbnail ? $thumbnail->getDocument() : null,
            'isPublished' => $node->isPublished(),
            'nodesEditPage' => $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]),
            'nodeType' => [
                'color' => $node->getNodeType()?->getColor() ?? '#000000',
            ]
        ];

        $parent = $this->nodeSource->getParent();

        if ($parent instanceof NodesSources) {
            $result['parent'] = [
                'title' => $parent->getTitle()
            ];
            $subparent = $parent->getParent();
            if ($subparent instanceof NodesSources) {
                $result['subparent'] = [
                    'title' => $subparent->getTitle()
                ];
            }
        }

        return $result;
    }
}
