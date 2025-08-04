<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ImageCropAlignmentType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'image_crop_alignment',
            'required' => false,
            'placeholder' => 'image_crop_alignment.none',
            'expanded' => true,
            'choices' => [
                'image_crop_alignment.top-left' => 'top-left',
                'image_crop_alignment.top' => 'top',
                'image_crop_alignment.top-right' => 'top-right',
                'image_crop_alignment.left' => 'left',
                'image_crop_alignment.center' => 'center',
                'image_crop_alignment.right' => 'right',
                'image_crop_alignment.bottom-left' => 'bottom-left',
                'image_crop_alignment.bottom' => 'bottom',
                'image_crop_alignment.bottom-right' => 'bottom-right',
            ],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'image_crop_alignment';
    }

    #[\Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
