<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Form;

use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Font variants selector form field type.
 */
class FontVariantsType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_flip(Font::$variantToHuman),
        ]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'font_variants';
    }
}
