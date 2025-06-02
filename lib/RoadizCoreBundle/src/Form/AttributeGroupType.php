<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AttributeGroupType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('canonicalName', TextType::class, [
            'label' => 'attribute_group.form.canonicalName',
            'empty_data' => '',
        ])
            ->add('attributeGroupTranslations', CollectionType::class, [
                'label' => 'attribute_group.form.attributeGroupTranslations',
                'allow_add' => true,
                'required' => false,
                'allow_delete' => true,
                'entry_type' => AttributeGroupTranslationType::class,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'uk-form uk-form-horizontal',
                    ],
                ],
                'attr' => [
                    'class' => 'rz-collection-form-type',
                ],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AttributeGroup::class);
    }
}
