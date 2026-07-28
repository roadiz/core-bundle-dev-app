<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Form\ExtendedBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeTypesFieldDecoratorType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'nodeType.displayName',
                'help' => 'human_readable_field_name',
                'empty_data' => '',
            ])
            ->add('description', TextType::class, [
                'label' => 'description',
                'required' => false,
            ])
            ->add('placeholder', TextType::class, [
                'label' => 'placeholder',
                'required' => false,
                'help' => 'label_for_field_with_empty_data',
            ])
            ->add('visible', ExtendedBooleanType::class, [
                'label' => 'visible',
                'required' => false,
                'help' => 'disable_field_visibility_if_you_dont_want_it_to_be_editable_from_backoffice',
            ])
            ->add('universal', ExtendedBooleanType::class, [
                'label' => 'universal',
                'required' => false,
                'help' => 'universal_fields_will_be_only_editable_from_default_translation',
            ])
            ->add('minLength', IntegerType::class, [
                'label' => 'nodeTypeField.minLength',
                'required' => false,
                'attr' => [
                    'placeholder' => 'no_limit',
                ],
            ])
            ->add('maxLength', IntegerType::class, [
                'label' => 'nodeTypeField.maxLength',
                'required' => false,
                'attr' => [
                    'placeholder' => 'no_limit',
                ],
            ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'nodetypefielddecorator';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'name' => '',
            'data_class' => NodeTypeField::class,
            'attr' => [
                'class' => 'rz-form node-type-form',
            ],
        ]);
    }
}
