<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodeSourceExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly NodesSources $nodeSource,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    #[\Override]
    public function getId(): int
    {
        return $this->nodeSource->getNode()->getId() ?? throw new \RuntimeException('NodeSource has no Node ID associated.');
    }

    #[\Override]
    public function getAlternativeDisplayable(): ?string
    {
        $parent = $this->nodeSource->getParent();

        if (!$parent instanceof NodesSources) {
            return null;
        }

        $items = [];
        $items[] = $parent->getTitle();

        $subParent = $parent->getParent();
        if ($subParent instanceof NodesSources) {
            $items[] = $subParent->getTitle();
        }

        return implode(' / ', array_reverse($items));
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->nodeSource->getTitle() ?? $this->nodeSource->getNode()->getNodeName();
    }

    #[\Override]
    public function getOriginal(): NodesSources
    {
        return $this->nodeSource;
    }

    #[\Override]
    public function getEditItemPath(): ?string
    {
        /** @var Translation $translation */
        $translation = $this->nodeSource->getTranslation();

        if ($this->security->isGranted(NodeVoter::EDIT_CONTENT, $this->nodeSource)) {
            return $this->urlGenerator->generate('nodesEditSourcePage', [
                'nodeId' => $this->nodeSource->getNode()->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        return null;
    }

    #[\Override]
    public function getThumbnail(): ?DocumentInterface
    {
        /** @var NodesSourcesDocuments|false $thumbnail */
        $thumbnail = $this->nodeSource->getDocumentsByFields()->first();

        return $thumbnail ? $thumbnail->getDocument() : null;
    }

    #[\Override]
    public function isPublished(): bool
    {
        return $this->nodeSource->getNode()->isPublished();
    }

    #[\Override]
    public function getColor(): string
    {
        return $this->nodeSource->getNodeTypeColor();
    }
}
