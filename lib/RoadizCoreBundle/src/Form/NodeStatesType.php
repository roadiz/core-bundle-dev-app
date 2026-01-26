<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeStatesType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => NodeStatus::class,
            'placeholder' => 'ignore',
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return EnumType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'node_statuses';
    }
}
