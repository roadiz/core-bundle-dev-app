<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Widget;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class TreeWidgetFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $managerRegistry,
        private DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    public function createNodeTree(?Node $root = null, ?TranslationInterface $translation = null): NodeTreeWidget
    {
        return new NodeTreeWidget(
            $this->requestStack,
            $this->managerRegistry,
            $this->nodeTypesBag,
            $root,
            $translation
        );
    }

    public function createRootNodeTree(?Node $root = null, ?TranslationInterface $translation = null): NodeTreeWidget
    {
        return new NodeTreeWidget(
            $this->requestStack,
            $this->managerRegistry,
            $this->nodeTypesBag,
            $root,
            $translation,
            true
        );
    }

    public function createTagTree(?Tag $root = null, ?TranslationInterface $translation = null): TagTreeWidget
    {
        return new TagTreeWidget(
            $this->requestStack,
            $this->managerRegistry,
            $root,
            $translation
        );
    }

    public function createFolderTree(?Folder $root = null, ?TranslationInterface $translation = null): FolderTreeWidget
    {
        return new FolderTreeWidget(
            $this->requestStack,
            $this->managerRegistry,
            $root,
            $translation
        );
    }
}
