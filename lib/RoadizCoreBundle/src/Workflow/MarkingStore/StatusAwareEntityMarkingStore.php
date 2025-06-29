<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow\MarkingStore;

use RZ\Roadiz\CoreBundle\Entity\StatusAwareEntityInterface;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

final class StatusAwareEntityMarkingStore implements MarkingStoreInterface
{
    public const string DRAFT = 'draft';
    public const string PUBLISHED = 'published';
    public const string DELETED = 'deleted';

    #[\Override]
    public function getMarking(object $subject): Marking
    {
        if (!$subject instanceof StatusAwareEntityInterface) {
            throw new \InvalidArgumentException('Subject must implement StatusAwareEntityInterface.');
        }

        return match (true) {
            $subject->isPublished() => new Marking([self::PUBLISHED => 1]),
            $subject->isDeleted() => new Marking([self::DELETED => 1]),
            default => new Marking([self::DRAFT => 1]), // Default to draft if no other state is set
        };
    }

    #[\Override]
    public function setMarking(object $subject, Marking $marking, array $context = []): void
    {
        if (!$subject instanceof StatusAwareEntityInterface) {
            throw new \InvalidArgumentException('Subject must implement StatusAwareEntityInterface.');
        }

        switch (key($marking->getPlaces())) {
            case self::PUBLISHED:
                if (!$subject->isPublished()) {
                    // Only set publishedAt if it was not already set
                    $subject->setPublishedAt(new \DateTime());
                }
                $subject->setDeletedAt(null);
                break;
            case self::DRAFT:
                $subject->setPublishedAt(null);
                $subject->setDeletedAt(null);
                break;
            case self::DELETED:
                if (!$subject->isDeleted()) {
                    // Only set deletedAd if it was not already set
                    $subject->setDeletedAt(new \DateTime());
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid marking state: '.key($marking->getPlaces()));
        }
    }
}
