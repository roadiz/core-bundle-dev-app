<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ObjectRepository;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\RozierBundle\Model\PositionDto;

trait UpdatePositionTrait
{
    /**
     * @param ObjectRepository<LeafInterface> $repository
     */
    protected function updatePositionAndParent(PositionDto $positionDto, LeafInterface $item, ObjectRepository $repository): void
    {
        /*
         * First, we set the new parent
         */
        if (null !== $positionDto->newParentId && $positionDto->newParentId > 0) {
            $parent = $repository->find($positionDto->newParentId);
            if (null !== $parent) {
                $item->setParent($parent);
            }
        } else {
            $item->setParent(null);
        }

        /*
         * Then compute new position
         */
        $this->updatePosition($positionDto, $item, $repository);
    }

    /**
     * @param ObjectRepository<PositionedInterface> $repository
     */
    public function updatePosition(PositionDto $positionDto, PositionedInterface $item, ObjectRepository $repository): void
    {
        /*
         * Then compute new position
         */
        $newPosition = $this->parsePosition($positionDto, $repository);
        if (null !== $newPosition) {
            $item->setPosition($newPosition);
        }
    }

    /**
     * @param ObjectRepository<PositionedInterface> $repository
     */
    protected function parsePosition(PositionDto $positionDto, ObjectRepository $repository): ?float
    {
        if ($positionDto->firstPosition) {
            return -0.5;
        }
        if ($positionDto->lastPosition) {
            return 99999999;
        }
        if (null !== $positionDto->nextId && $positionDto->nextId > 0) {
            $nextNode = $repository->find($positionDto->nextId);
            if (null !== $nextNode) {
                return $nextNode->getPosition() - 0.5;
            }
        } elseif (null !== $positionDto->prevId && $positionDto->prevId > 0) {
            $prevNode = $repository->find($positionDto->prevId);
            if (null !== $prevNode) {
                return $prevNode->getPosition() + 0.5;
            }
        }

        return null;
    }
}
