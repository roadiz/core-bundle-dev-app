<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class NodeSourceCollectionType extends CollectionType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /*
         * We need to flatten form data array keys to force numeric array in database
         */
        $builder->addEventListener(FormEvents::SUBMIT, $this->onSubmit(...), 40);
    }

    public function onSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $event->setData(array_values($data));
    }
}
