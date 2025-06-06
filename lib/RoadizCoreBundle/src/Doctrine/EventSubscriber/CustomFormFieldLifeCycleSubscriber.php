<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\EntityHandler\CustomFormFieldHandler;

#[AsDoctrineListener('prePersist')]
final readonly class CustomFormFieldLifeCycleSubscriber
{
    public function __construct(private CustomFormFieldHandler $customFormFieldHandler)
    {
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $field = $event->getObject();
        if ($field instanceof CustomFormField) {
            /*
             * Automatically set position only if not manually set before.
             */
            if (0.0 === $field->getPosition()) {
                /*
                 * Get the last index after last node in parent
                 */
                $this->customFormFieldHandler->setCustomFormField($field);
                $lastPosition = $this->customFormFieldHandler->cleanPositions(false);
                if ($lastPosition > 1) {
                    /*
                     * Need to decrement position because current field is already
                     * in custom-form field collection count.
                     */
                    $field->setPosition($lastPosition - 1);
                } else {
                    $field->setPosition($lastPosition);
                }
            }
        }
    }
}
