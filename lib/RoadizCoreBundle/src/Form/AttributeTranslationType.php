<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\AttributeTranslation;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\TranslationTransformer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

final class AttributeTranslationType extends AbstractType
{
    public function __construct(
        private readonly TranslationTransformer $translationTransformer,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('label', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
            'required' => false,
        ])
            ->add('translation', TranslationsType::class, [
                'label' => 'translation',
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('options', CollectionType::class, [
                'label' => 'attributes.form.options',
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'required' => false,
                ],
                'attr' => [
                    'class' => 'rz-collection-form-type',
                    'no-field-group' => true,
                ],
            ])
        ;

        $builder->get('translation')->addModelTransformer($this->translationTransformer);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', AttributeTranslation::class);
        $resolver->setDefault('constraints', [
            // Keep this constraint as class annotation is not validated
            new UniqueEntity([
                'fields' => ['attribute', 'translation'],
                'errorPath' => 'translation',
            ]),
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'attribute_translation';
    }
}
