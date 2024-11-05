<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentLimitationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('copyrightValidSince', DateTimeType::class, [
                'label' => 'document.copyrightValidSince',
                'required' => false,
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ])
            ->add('copyrightValidUntil', DateTimeType::class, [
                'label' => 'document.copyrightValidUntil',
                'required' => false,
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);

        $resolver->setRequired('referer');
        $resolver->setAllowedTypes('referer', ['null', 'string']);
    }
}
