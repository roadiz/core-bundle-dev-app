<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodeSourceModel extends AbstractExplorerItem
{
    public function __construct(
        private readonly NodesSources $nodeSource,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security
    ) {
    }

    #[Groups(['model', 'node'])]
    public function getId(): string|int
    {
        return $this->nodeSource->getNode()->getId();
    }

    #[Groups(['model', 'node'])]
    public function getAlternativeDisplayable(): ?string
    {
        $parent = $this->nodeSource->getParent();

        if (!($parent instanceof NodesSources)) {
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

    #[Groups(['model', 'node'])]
    public function getDisplayable(): string
    {
        return $this->nodeSource->getTitle() ?? $this->nodeSource->getNode()->getNodeName();
    }

    #[Exclude]
    public function getOriginal(): NodesSources
    {
        return $this->nodeSource;
    }

    #[Groups(['model', 'node'])]
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

    #[Groups(['model', 'node'])]
    public function getThumbnail(): ?DocumentInterface
    {
        /** @var NodesSourcesDocuments|false $thumbnail */
        $thumbnail = $this->nodeSource->getDocumentsByFields()->first();
        return $thumbnail ? $thumbnail->getDocument() : null;
    }

    #[Groups(['model', 'node'])]
    public function isPublished(): bool
    {
        return $this->nodeSource->getNode()->isPublished();
    }

    #[Groups(['model', 'node'])]
    public function getColor(): string
    {
        return $this->nodeSource->getNode()->getNodeType()->getColor() ?? '#000000';
    }
}
