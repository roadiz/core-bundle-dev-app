<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Role::class);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'role';
    }
}
