<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
        ])
        ->add('locale', ChoiceType::class, [
            'label' => 'locale',
            'required' => true,
            'choices' => array_flip(Translation::$availableLocales),
        ])
        ->add('available', CheckboxType::class, [
            'label' => 'available',
            'required' => false,
        ])
        ->add('overrideLocale', TextType::class, [
            'label' => 'overrideLocale',
            'required' => false,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'translation';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'locale' => '',
            'overrideLocale' => '',
            'data_class' => Translation::class,
            'attr' => [
                'class' => 'rz-form translation-form',
            ],
        ]);
    }
}
