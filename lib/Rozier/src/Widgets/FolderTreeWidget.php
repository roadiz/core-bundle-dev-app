<?php

declare(strict_types=1);

namespace Themes\Rozier\Widgets;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepare a Folder tree according to Folder hierarchy and given options.
 */
final class FolderTreeWidget extends AbstractWidget
{
    protected ?Folder $parentFolder = null;
    protected ?iterable $folders = null;

    /**
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     * @param Folder|null $parent
     */
    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        ?Folder $parent = null
    ) {
        parent::__construct($requestStack, $managerRegistry);
        $this->parentFolder = $parent;
    }

    /**
     * @param Folder $parent
     * @return array
     */
    public function getChildrenFolders(Folder $parent): array
    {
        return $this->folders = $this->getManagerRegistry()
                    ->getRepository(Folder::class)
                    ->findByParentAndTranslation($parent, $this->getTranslation());
    }
    /**
     * @return Folder|null
     */
    public function getRootFolder(): ?Folder
    {
        return $this->parentFolder;
    }

    /**
     * @return iterable<Folder>
     */
    public function getFolders(): iterable
    {
        if (null === $this->folders) {
            $this->folders = $this->getManagerRegistry()
                ->getRepository(Folder::class)
                ->findByParentAndTranslation($this->getRootFolder(), $this->getTranslation());
        }
        return $this->folders;
    }
}
