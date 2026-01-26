<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
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
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly ?string $locale = null,
    ) {
    }

    #[\Override]
    public function getId(): int
    {
        return $this->node->getId() ?? throw new \RuntimeException('Node has no ID associated.');
    }

    private function getNodeSource(Node $node): ?NodesSources
    {
        if (null !== $this->locale) {
            $nodeSource = $node->getNodeSources()->filter(
                fn (NodesSources $nodeSource) => $nodeSource->getTranslation()->getPreferredLocale() === $this->locale
            )->first() ?: null;
            if (null !== $nodeSource) {
                return $nodeSource;
            }
        }

        // If no locale is specified, return the default translation
        return $node->getNodeSources()->filter(
            fn (NodesSources $nodeSource) => $nodeSource->getTranslation()->isDefaultTranslation()
        )->first() ?: null;
    }

    #[\Override]
    public function getAlternativeDisplayable(): ?string
    {
        $parent = $this->node->getParent();

        if (!$parent instanceof Node) {
            return null;
        }

        $items = [];
        $items[] = $this->getNodeSource($parent)?->getTitle() ?? $parent->getNodeName();

        $subParent = $parent->getParent();
        if ($subParent instanceof Node) {
            $items[] = $this->getNodeSource($subParent)?->getTitle() ?? $subParent->getNodeName();
        }

        return implode(' / ', array_reverse($items));
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->getNodeSource($this->node)?->getTitle() ?? $this->node->getNodeName();
    }

    #[\Override]
    public function getOriginal(): Node
    {
        return $this->node;
    }

    #[\Override]
    public function getEditItemPath(): ?string
    {
        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->getNodeSource($this->node);

        if (null === $nodeSource) {
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

    #[\Override]
    public function getThumbnail(): ?DocumentInterface
    {
        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->getNodeSource($this->node);

        /** @var NodesSourcesDocuments|null $thumbnail */
        $thumbnail = $nodeSource?->getDocumentsByFields()->first() ?: null;

        return $thumbnail?->getDocument() ?? null;
    }

    #[\Override]
    public function isPublished(): bool
    {
        return $this->node->isPublished();
    }

    #[\Override]
    public function getColor(): string
    {
        return $this->nodeTypesBag->get($this->node->getNodeTypeName())?->getColor() ?? '#000000';
    }
}
