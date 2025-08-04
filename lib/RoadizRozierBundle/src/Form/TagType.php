<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tagName', TextType::class, [
            'label' => 'tagName',
            'empty_data' => '',
            'help' => 'tag.tagName.help',
        ])

        ->add('locked', CheckboxType::class, [
            'label' => 'locked',
            'help' => 'tag.locked.help',
            'required' => false,
        ])
        ->add('visible', CheckboxType::class, [
            'label' => 'visible',
            'required' => false,
        ])
        ->add('color', ColorType::class, [
            'label' => 'tag.color',
            'required' => false,
            'html5' => true,
        ])
        ->add('childrenOrder', ChoiceType::class, [
            'label' => 'tag.childrenOrder',
            'choices' => [
                'position' => 'position',
                'tagName' => 'tagName',
                'createdAt' => 'createdAt',
                'updatedAt' => 'updatedAt',
            ],
        ])
        ->add('childrenOrderDirection', ChoiceType::class, [
            'label' => 'tag.childrenOrderDirection',
            'choices' => [
                'ascendant' => 'ASC',
                'descendant' => 'DESC',
            ],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'tag';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'tagName' => '',
            'data_class' => Tag::class,
            'attr' => [
                'class' => 'uk-form tag-form',
            ],
        ]);

        $resolver->setAllowedTypes('tagName', 'string');
    }
}
