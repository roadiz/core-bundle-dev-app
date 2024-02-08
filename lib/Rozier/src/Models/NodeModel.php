<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @Serializer\ExclusionPolicy("all")
 */
final class NodeModel implements ModelInterface
{
    public function __construct(
        private Node $node,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function toArray(): array
    {
        /** @var NodesSources|false $nodeSource */
        $nodeSource = $this->node->getNodeSources()->first();

        if (false === $nodeSource) {
            $result = [
                'id' => $this->node->getId(),
                'title' => $this->node->getNodeName(),
                'nodeName' => $this->node->getNodeName(),
                'isPublished' => $this->node->isPublished(),
                'nodeType' => [
                    'color' => $this->node->getNodeType()?->getColor() ?? '#000000',
                ]
            ];
            if ($this->security->isGranted(NodeVoter::EDIT_SETTING, $this->node)) {
                $result['nodesEditPage'] = $this->urlGenerator->generate('nodesEditPage', [
                    'nodeId' => $this->node->getId(),
                ]);
            }
            return $result;
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
            'nodeType' => [
                'color' => $this->node->getNodeType()?->getColor() ?? '#000000',
            ]
        ];

        if ($this->security->isGranted(NodeVoter::EDIT_CONTENT, $nodeSource)) {
            $result['nodesEditPage'] = $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $this->node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        $parent = $this->node->getParent();

        if ($parent instanceof Node) {
            $result['parent'] = [
                'title' => $parent->getNodeSources()->first() ?
                    $parent->getNodeSources()->first()->getTitle() :
                    $parent->getNodeName()
            ];
            $subParent = $parent->getParent();
            if ($subParent instanceof Node) {
                $result['subparent'] = [
                    'title' => $subParent->getNodeSources()->first() ?
                        $subParent->getNodeSources()->first()->getTitle() :
                        $subParent->getNodeName()
                ];
            }
        }

        return $result;
    }
}
