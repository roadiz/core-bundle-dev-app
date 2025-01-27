<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodeExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly Node $node,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    public function getId(): string|int
    {
        return $this->node->getId();
    }

    public function getAlternativeDisplayable(): ?string
    {
        $parent = $this->node->getParent();

        if (!($parent instanceof Node)) {
            return null;
        }

        $items = [];
        $items[] = $parent->getNodeSources()->first() ?
                $parent->getNodeSources()->first()->getTitle() :
                $parent->getNodeName();

        $subParent = $parent->getParent();
        if ($subParent instanceof Node) {
            $items[] = $subParent->getNodeSources()->first() ?
                $subParent->getNodeSources()->first()->getTitle() :
                $subParent->getNodeName();
        }

        return implode(' / ', array_reverse($items));
    }

    public function getDisplayable(): string
    {
        /** @var NodesSources|false $nodeSource */
        $nodeSource = $this->node->getNodeSources()->first();

        return false !== $nodeSource ?
            ($nodeSource->getTitle() ?? $this->node->getNodeName()) :
            $this->node->getNodeName();
    }

    public function getOriginal(): Node
    {
        return $this->node;
    }

    public function getEditItemPath(): ?string
    {
        /** @var NodesSources|false $nodeSource */
        $nodeSource = $this->node->getNodeSources()->first();

        if (false === $nodeSource) {
            if ($this->security->isGranted(NodeVoter::EDIT_SETTING, $this->node)) {
                return $this->urlGenerator->generate('nodesEditPage', [
                    'nodeId' => $this->node->getId(),
                ]);
            }

            return null;
        }

        /** @var Translation $translation */
        $translation = $nodeSource->getTranslation();

        if ($this->security->isGranted(NodeVoter::EDIT_CONTENT, $nodeSource)) {
            return $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $this->node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        return null;
    }

    public function getThumbnail(): ?DocumentInterface
    {
        /** @var NodesSources|false $nodeSource */
        $nodeSource = $this->node->getNodeSources()->first();
        /** @var NodesSourcesDocuments|false $thumbnail */
        $thumbnail = false !== $nodeSource ? $nodeSource->getDocumentsByFields()->first() : false;

        return $thumbnail ? $thumbnail->getDocument() : null;
    }

    public function isPublished(): bool
    {
        return $this->node->isPublished();
    }

    public function getColor(): string
    {
        return $this->nodeTypesBag->get($this->node->getNodeTypeName())->getColor() ?? '#000000';
    }
}
