<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Form\MarkdownType;
use RZ\Roadiz\CoreBundle\Form\TagTranslationDocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TagTranslationType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
            'constraints' => [
                new NotNull(),
                new NotBlank(),
                // Allow users to rename Tag the same, but tag slug must be different!
                new Length([
                    'max' => 255,
                ]),
            ],
        ])
            ->add('description', MarkdownType::class, [
                'label' => 'description',
                'required' => false,
            ])
            ->add('tagTranslationDocuments', TagTranslationDocumentType::class, [
                'label' => 'documents',
                'required' => false,
                'tagTranslation' => $builder->getForm()->getData(),
            ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'tag_translation';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'tagName' => '',
            'data_class' => TagTranslation::class,
            'attr' => [
                'class' => 'uk-form tag-translation-form',
            ],
        ]);
        $resolver->setAllowedTypes('tagName', 'string');
    }
}
