<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Events;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;

/**
 * @package RZ\Roadiz\Core\Events
 */
class LeafEntityLifeCycleSubscriber implements EventSubscriber
{
    private HandlerFactoryInterface $handlerFactory;

    public function __construct(HandlerFactoryInterface $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     * @return void
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof AbstractEntity && $entity instanceof LeafInterface) {
            /*
             * Automatically set position only if not manually set before.
             */
            try {
                $handler = $this->handlerFactory->getHandler($entity);

                if ($entity->getPosition() === 0.0) {
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
                } elseif ($entity->getPosition() === 0.5) {
                    /*
                     * Position is set to 0.5, so we need to
                     * shift all tags to the bottom.
                     */
                    $handler->cleanPositions(true);
                }
            } catch (\InvalidArgumentException $e) {
            }
        }
    }
}
