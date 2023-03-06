<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\EntityHandler\CustomFormFieldHandler;

final class CustomFormFieldLifeCycleSubscriber implements EventSubscriber
{
    private CustomFormFieldHandler $customFormFieldHandler;

    /**
     * @param CustomFormFieldHandler $customFormFieldHandler
     */
    public function __construct(CustomFormFieldHandler $customFormFieldHandler)
    {
        $this->customFormFieldHandler = $customFormFieldHandler;
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
     */
    public function prePersist(LifecycleEventArgs $event): void
    {
        $field = $event->getObject();
        if ($field instanceof CustomFormField) {
            /*
             * Automatically set position only if not manually set before.
             */
            if ($field->getPosition() === 0.0) {
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
