<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('folderName', TextType::class, [
            'label' => 'folder.name',
            'empty_data' => '',
        ])
        ->add('visible', CheckboxType::class, [
            'label' => 'visible',
            'required' => false,
        ])
        ->add('locked', CheckboxType::class, [
            'label' => 'locked',
            'help' => 'folder.locked.help',
            'required' => false,
        ])
        ->add('color', ColorType::class, [
            'label' => 'folder.color',
            'required' => false,
            'html5' => true,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'folder';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'name' => '',
            'data_class' => Folder::class,
            'attr' => [
                'class' => 'uk-form folder-form',
            ],
        ]);
    }
}
