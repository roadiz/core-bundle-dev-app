<?php

declare(strict_types=1);

namespace Themes\Rozier;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Themes\Rozier\Widgets\FolderTreeWidget;
use Themes\Rozier\Widgets\NodeTreeWidget;
use Themes\Rozier\Widgets\TagTreeWidget;
use Themes\Rozier\Widgets\TreeWidgetFactory;

final class RozierServiceRegistry
{
    private ?array $settingGroups = null;
    private ?TagTreeWidget $tagTree = null;
    private ?FolderTreeWidget $folderTree = null;
    private ?NodeTreeWidget $nodeTree = null;

    public function __construct(
        private readonly Settings $settingsBag,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly NodeChrootResolver $chrootResolver,
        private readonly array $backofficeMenuEntries,
    ) {
    }

    public function getMaxFilesize(): int|float
    {
        return UploadedFile::getMaxFilesize();
    }

    public function getAdminImage(): ?DocumentInterface
    {
        return $this->settingsBag->getDocument('admin_image');
    }

    public function getSettingGroups(): array
    {
        if (null === $this->settingGroups) {
            $this->settingGroups = $this->managerRegistry->getRepository(SettingGroup::class)
                ->findBy(
                    ['inMenu' => true],
                    ['name' => 'ASC']
                );
        }

        return $this->settingGroups;
    }

    public function getTagTree(): TagTreeWidget
    {
        if (null === $this->tagTree) {
            $this->tagTree = $this->treeWidgetFactory->createTagTree();
        }

        return $this->tagTree;
    }

    public function getFolderTree(): FolderTreeWidget
    {
        if (null === $this->folderTree) {
            $this->folderTree = $this->treeWidgetFactory->createFolderTree();
        }

        return $this->folderTree;
    }

    public function getNodeTree(mixed $user): NodeTreeWidget
    {
        if (null === $this->nodeTree) {
            $this->nodeTree = $this->treeWidgetFactory->createNodeTree(
                $this->chrootResolver->getChroot($user)
            );
        }

        return $this->nodeTree;
    }

    public function getBackofficeMenuEntries(): array
    {
        return $this->backofficeMenuEntries;
    }
}
