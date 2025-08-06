<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

final class NodeTypeFieldSerializationType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('excludedFromSerialization', CheckboxType::class, [
            'label' => 'nodeTypeField.excludedFromSerialization',
            'help' => 'exclude_this_field_from_api_serialization',
            'required' => false,
        ])
        ->add('serializationGroups', CollectionType::class, [
            'label' => 'nodeTypeField.serializationGroups',
            'help' => 'nodeTypeField.serializationGroups.help',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'attr' => [
                'class' => 'rz-collection-form-type',
            ],
            'entry_options' => [
                'label' => false,
            ],
            'entry_type' => TextType::class,
        ])
        ->add('normalizationContextGroups', CollectionType::class, [
            'label' => 'nodeTypeField.normalizationContextGroups',
            'help' => 'nodeTypeField.normalizationContextGroups.help',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'attr' => [
                'class' => 'rz-collection-form-type',
            ],
            'entry_options' => [
                'label' => false,
            ],
            'entry_type' => TextType::class,
        ])
        ->add('serializationMaxDepth', IntegerType::class, [
            'label' => 'nodeTypeField.serializationMaxDepth',
            'required' => false,
            'attr' => [
                'placeholder' => 'default_value',
            ],
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                ]),
            ],
        ])
        ->add('serializationExclusionExpression', TextareaType::class, [
            'label' => 'nodeTypeField.serializationExclusionExpression',
            'required' => false,
            'help' => 'exclude_this_field_from_api_serialization_if_expression_result_is_true',
            'attr' => [
                'placeholder' => 'enter_symfony_expression_language_with_object_as_var_name',
            ],
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'nodeTypeField.serialization',
            'inherit_data' => true,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return '';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
