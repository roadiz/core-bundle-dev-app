<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Node state selector form field type.
 */
class ExtendedBooleanType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'true' => true,
                'false' => false,
            ],
            'placeholder' => 'ignore',
            'required' => false,
            'expanded' => true,
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'extendedboolean';
    }
}
