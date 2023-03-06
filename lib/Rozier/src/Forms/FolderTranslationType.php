<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package Themes\Rozier\Forms
 */
class FolderTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'folder_translation';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => FolderTranslation::class,
            'attr' => [
                'class' => 'uk-form folder-form',
            ],
        ]);
    }
}
