<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Serializer\ExclusionPolicy("all")
 */
final class NodeSourceModel implements ModelInterface
{
    public function __construct(
        private NodesSources $nodeSource,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
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
            'nodeType' => [
                'color' => $node->getNodeType()?->getColor() ?? '#000000',
            ]
        ];

        if ($this->security->isGranted(NodeVoter::EDIT_CONTENT, $node)) {
            $result['nodesEditPage'] = $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

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
