<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Events;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;

#[AsDoctrineListener(event: Events::prePersist)]
final readonly class LeafEntityLifeCycleSubscriber
{
    public function __construct(private HandlerFactoryInterface $handlerFactory)
    {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!($entity instanceof PersistableInterface) || !($entity instanceof LeafInterface)) {
            return;
        }

        /*
         * Automatically set position only if not manually set before.
         */
        try {
            $handler = $this->handlerFactory->getHandler($entity);

            if (0.0 === $entity->getPosition()) {
                /*
                 * Get the last index after last tag in parent
                 */
                $lastPosition = $handler->cleanPositions(false);
                if ($lastPosition > 1 && null !== $entity->getParent()) {
                    /*
                     * Need to decrement position because current tag is already
                     * in parent's children collection count.
                     */
                    $entity->setPosition($lastPosition - 1);
                } else {
                    $entity->setPosition($lastPosition);
                }
            } elseif (0.5 === $entity->getPosition()) {
                /*
                 * Position is set to 0.5, so we need to
                 * shift all tags to the bottom.
                 */
                $handler->cleanPositions(true);
            }
        } catch (\InvalidArgumentException) {
        }
    }
}
