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
    private Settings $settingsBag;
    private ManagerRegistry $managerRegistry;
    private TreeWidgetFactory $treeWidgetFactory;
    private NodeChrootResolver $chrootResolver;
    private array $backofficeMenuEntries;

    private ?array $settingGroups = null;
    private ?TagTreeWidget $tagTree = null;
    private ?FolderTreeWidget $folderTree = null;
    private ?NodeTreeWidget $nodeTree = null;

    /**
     * @param Settings $settingsBag
     * @param ManagerRegistry $managerRegistry
     * @param TreeWidgetFactory $treeWidgetFactory
     * @param NodeChrootResolver $chrootResolver
     * @param array $backofficeMenuEntries
     */
    public function __construct(
        Settings $settingsBag,
        ManagerRegistry $managerRegistry,
        TreeWidgetFactory $treeWidgetFactory,
        NodeChrootResolver $chrootResolver,
        array $backofficeMenuEntries
    ) {
        $this->settingsBag = $settingsBag;
        $this->managerRegistry = $managerRegistry;
        $this->treeWidgetFactory = $treeWidgetFactory;
        $this->chrootResolver = $chrootResolver;
        $this->backofficeMenuEntries = $backofficeMenuEntries;
    }

    /**
     * @return int|float
     */
    public function getMaxFilesize()
    {
        return UploadedFile::getMaxFilesize();
    }

    /**
     * @return DocumentInterface|null
     */
    public function getAdminImage(): ?DocumentInterface
    {
        return $this->settingsBag->getDocument('admin_image');
    }

    /**
     * @return array
     */
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

    /**
     * @param mixed $user
     * @return NodeTreeWidget
     */
    public function getNodeTree($user): NodeTreeWidget
    {
        if (null === $this->nodeTree) {
            $this->nodeTree = $this->treeWidgetFactory->createNodeTree(
                $this->chrootResolver->getChroot($user)
            );
        }
        return $this->nodeTree;
    }

    /**
     * @return array
     */
    public function getBackofficeMenuEntries(): array
    {
        return $this->backofficeMenuEntries;
    }
}
