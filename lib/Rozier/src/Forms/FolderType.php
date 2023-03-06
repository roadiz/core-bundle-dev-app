<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Form\ColorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package Themes\Rozier\Forms
 */
class FolderType extends AbstractType
{
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
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'folder';
    }

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
