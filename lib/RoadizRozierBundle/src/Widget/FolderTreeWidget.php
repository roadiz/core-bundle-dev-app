<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Widget;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepare a Folder tree according to Folder hierarchy and given options.
 */
final class FolderTreeWidget extends AbstractWidget
{
    private ?iterable $folders = null;

    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        private readonly ?Folder $parentFolder = null,
        private readonly ?TranslationInterface $translation = null,
    ) {
        parent::__construct($requestStack, $managerRegistry);
    }

    public function getChildrenFolders(Folder $parent): array
    {
        return $this->folders = $this->getManagerRegistry()
                    ->getRepository(Folder::class)
                    ->findByParentAndTranslation($parent, $this->getTranslation());
    }

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

    #[\Override]
    public function getTranslation(): TranslationInterface
    {
        return $this->translation ?? parent::getTranslation();
    }
}
