<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeTypeFieldType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
            'help' => 'technical_name_for_database_and_templating',
        ])
        ->add('label', TextType::class, [
            'label' => 'label',
            'help' => 'human_readable_field_name',
            'empty_data' => '',
        ])
        ->add('type', ChoiceType::class, [
            'label' => 'type',
            'required' => true,
            'choices' => array_flip(FieldType::humanValues()),
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
        ->add('groupName', TextType::class, [
            'label' => 'groupName',
            'required' => false,
            'help' => 'use_the_same_group_names_over_fields_to_gather_them_in_tabs',
        ])
        ->add('visible', CheckboxType::class, [
            'label' => 'visible',
            'required' => false,
            'help' => 'disable_field_visibility_if_you_dont_want_it_to_be_editable_from_backoffice',
        ])
        ->add('indexed', CheckboxType::class, [
            'label' => 'indexed',
            'required' => false,
            'help' => 'field_should_be_indexed_if_you_plan_to_query_or_order_by_it',
        ])
        ->add('universal', CheckboxType::class, [
            'label' => 'universal',
            'required' => false,
            'help' => 'universal_fields_will_be_only_editable_from_default_translation',
        ])
        ->add('expanded', CheckboxType::class, [
            'label' => 'expanded',
            'help' => 'use_checkboxes_or_radio_buttons_instead_of_select_box',
            'required' => false,
        ])
        ->add('excludeFromSearch', CheckboxType::class, [
            'label' => 'excludeFromSearch',
            'help' => 'exclude_this_field_from_fulltext_search_engine',
            'required' => false,
        ])
        ->add('defaultValues', DynamicType::class, [
            'label' => 'nodeTypeField.defaultValues',
            'required' => false,
            'help' => 'for_children_node_and_node_references_enter_node_type_names_comma_separated',
            'attr' => [
                'placeholder' => 'enter_values_comma_separated',
            ],
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
        ->add('serialization', NodeTypeFieldSerializationType::class, [
            'data_class' => NodeTypeField::class,
            'required' => false,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'nodetypefield';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'fieldName' => '',
            'nodeType' => null,
            'data_class' => NodeTypeField::class,
            'attr' => [
                'class' => 'rz-form node-type-field-form',
            ],
        ]);
    }
}
