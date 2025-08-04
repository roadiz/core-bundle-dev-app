<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingGroupType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'name',
                'empty_data' => '',
            ]
        )
        ->add(
            'inMenu',
            CheckboxType::class,
            [
                'label' => 'settingGroup.in.menu',
                'required' => false,
            ]
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', SettingGroup::class);
    }
}
