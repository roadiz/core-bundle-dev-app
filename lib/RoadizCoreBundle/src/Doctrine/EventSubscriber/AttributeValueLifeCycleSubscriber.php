<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;

#[AsDoctrineListener('prePersist')]
#[AsDoctrineListener('onFlush')]
final class AttributeValueLifeCycleSubscriber
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof AttributeValueInterface) {
            if (
                null !== $entity->getAttribute()
                && null !== $entity->getAttribute()->getDefaultRealm()
            ) {
                $entity->setRealm($entity->getAttribute()->getDefaultRealm());
            }

            /*
             * Automatically set position only if not manually set before.
             */
            if (0.0 === $entity->getPosition()) {
                /*
                 * Get the last index after last node in parent
                 */
                $nodeAttributes = $entity->getAttributable()->getAttributeValues();
                $lastPosition = 1;
                foreach ($nodeAttributes as $nodeAttribute) {
                    $nodeAttribute->setPosition($lastPosition);
                    ++$lastPosition;
                }

                $entity->setPosition($lastPosition);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof AttributeValueInterface) {
                $classMetadata = $em->getClassMetadata(AttributeValue::class);
                foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {
                    if ('position' === $keyField) {
                        $nodeAttributes = $entity->getAttributable()->getAttributeValues();
                        /*
                         * Need to resort collection based on updated position.
                         */
                        $iterator = $nodeAttributes->getIterator();
                        if ($iterator instanceof \ArrayIterator) {
                            // define ordering closure, using preferred comparison method/field
                            $iterator->uasort(function (AttributeValueInterface $first, AttributeValueInterface $second) {
                                return $first->getPosition() > $second->getPosition() ? 1 : -1;
                            });
                        }

                        $lastPosition = 1;
                        /** @var AttributeValueInterface $nodeAttribute */
                        foreach ($iterator as $nodeAttribute) {
                            $nodeAttribute->setPosition($lastPosition);
                            $uow->computeChangeSet($classMetadata, $nodeAttribute);
                            ++$lastPosition;
                        }
                    }
                }
            }
        }
    }
}
