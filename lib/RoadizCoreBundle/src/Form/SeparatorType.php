<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Group selector form field type.
 */
class SeparatorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'separator';
    }
}
