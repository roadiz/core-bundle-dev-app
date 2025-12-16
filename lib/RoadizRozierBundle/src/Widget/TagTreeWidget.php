<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Widget;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepare a Tag tree according to Tag hierarchy and given options.
 */
final class TagTreeWidget extends AbstractWidget
{
    private ?iterable $tags = null;
    private bool $canReorder = true;

    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        private readonly ?Tag $parentTag = null,
        private readonly ?TranslationInterface $translation = null,
        private readonly bool $forceTranslation = false,
    ) {
        parent::__construct($requestStack, $managerRegistry);
    }

    /**
     * Fill twig assignation array with TagTree entities.
     */
    protected function getTagTreeAssignationForParent(): void
    {
        $ordering = [
            'position' => 'ASC',
        ];
        if (
            null !== $this->parentTag
            && 'order' !== $this->parentTag->getChildrenOrder()
            && 'position' !== $this->parentTag->getChildrenOrder()
        ) {
            $ordering = [
                $this->parentTag->getChildrenOrder() => $this->parentTag->getChildrenOrderDirection(),
            ];
            $this->canReorder = false;
        }
        $criteria = [
            'parent' => $this->parentTag,
        ];
        if ($this->forceTranslation) {
            $criteria['translation'] = $this->getTranslation();
        }
        $this->tags = $this->getTagRepository()->findBy($criteria, $ordering);
    }

    /**
     * @return iterable<Tag>|null
     */
    public function getChildrenTags(?Tag $parent): ?iterable
    {
        if (null !== $parent) {
            $ordering = [
                'position' => 'ASC',
            ];
            if (
                'order' !== $parent->getChildrenOrder()
                && 'position' !== $parent->getChildrenOrder()
            ) {
                $ordering = [
                    $parent->getChildrenOrder() => $parent->getChildrenOrderDirection(),
                ];
            }

            $criteria = [
                'parent' => $parent,
            ];
            if ($this->forceTranslation) {
                $criteria['translation'] = $this->getTranslation();
            }

            $this->tags = $this->getTagRepository()->findBy($criteria, $ordering);

            return $this->tags;
        }

        return null;
    }

    public function getRootTag(): ?Tag
    {
        return $this->parentTag;
    }

    /**
     * @return iterable<Tag>
     */
    public function getTags(): iterable
    {
        if (null === $this->tags) {
            $this->getTagTreeAssignationForParent();
        }

        return $this->tags ?? [];
    }

    protected function getTagRepository(): TagRepository
    {
        return $this->getManagerRegistry()->getRepository(Tag::class);
    }

    /**
     * Gets the value of canReorder.
     */
    public function getCanReorder(): bool
    {
        return $this->canReorder;
    }

    #[\Override]
    public function getTranslation(): TranslationInterface
    {
        return $this->translation ?? parent::getTranslation();
    }
}
