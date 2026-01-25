<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\RozierBundle\Event\UserActionsMenuEvent;
use RZ\Roadiz\RozierBundle\Widget\FolderTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\NodeTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TagTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RozierServiceRegistry
{
    private ?array $settingGroups = null;
    private ?TagTreeWidget $tagTree = null;
    private ?FolderTreeWidget $folderTree = null;
    private ?NodeTreeWidget $nodeTree = null;
    private ?array $userActions = null;

    public function __construct(
        private readonly Settings $settingsBag,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly NodeChrootResolver $chrootResolver,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly array $backofficeMenuEntries,
    ) {
    }

    public function getUserActions(): array
    {
        if (null === $this->userActions) {
            $userActionsMenuEvent = $this->eventDispatcher->dispatch(new UserActionsMenuEvent());
            $this->userActions = $userActionsMenuEvent->getActions();
        }

        return $this->userActions;
    }

    public function getMaxFilesize(): int|float
    {
        return UploadedFile::getMaxFilesize();
    }

    /** @deprecated */
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
