<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Form\GroupsType;
use Symfony\Component\Form\FormBuilderInterface;

class AddUserType extends UserType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('groups', GroupsType::class, [
            'label' => 'user.groups',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
        ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'add_user';
    }
}
