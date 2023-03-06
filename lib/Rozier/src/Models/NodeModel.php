<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @package Themes\Rozier\Models
 * @Serializer\ExclusionPolicy("all")
 */
final class NodeModel implements ModelInterface
{
    private Node $node;
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param Node $node
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Node $node, UrlGeneratorInterface $urlGenerator)
    {
        $this->node = $node;
        $this->urlGenerator = $urlGenerator;
    }

    public function toArray(): array
    {
        /** @var NodesSources|false $nodeSource */
        $nodeSource = $this->node->getNodeSources()->first();

        if (false === $nodeSource) {
            return [
                'id' => $this->node->getId(),
                'title' => $this->node->getNodeName(),
                'nodeName' => $this->node->getNodeName(),
                'isPublished' => $this->node->isPublished(),
                'nodesEditPage' => $this->urlGenerator->generate('nodesEditPage', [
                    'nodeId' => $this->node->getId(),
                ]),
                'nodeType' => [
                    'color' => $this->node->getNodeType()->getColor()
                ]
            ];
        }

        /** @var NodesSourcesDocuments|false $thumbnail */
        $thumbnail = $nodeSource->getDocumentsByFields()->first();
        /** @var Translation $translation */
        $translation = $nodeSource->getTranslation();

        $result = [
            'id' => $this->node->getId(),
            'title' => $nodeSource->getTitle() ?? $this->node->getNodeName(),
            'thumbnail' => $thumbnail ? $thumbnail->getDocument() : null,
            'nodeName' => $this->node->getNodeName(),
            'isPublished' => $this->node->isPublished(),
            'nodesEditPage' => $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $this->node->getId(),
                'translationId' => $translation->getId(),
            ]),
            'nodeType' => [
                'color' => $this->node->getNodeType()->getColor()
            ]
        ];

        $parent = $this->node->getParent();

        if ($parent instanceof Node) {
            $result['parent'] = [
                'title' => $parent->getNodeSources()->first()->getTitle()
            ];
            $subParent = $parent->getParent();
            if ($subParent instanceof Node) {
                $result['subparent'] = [
                    'title' => $subParent->getNodeSources()->first()->getTitle()
                ];
            }
        }

        return $result;
    }
}
