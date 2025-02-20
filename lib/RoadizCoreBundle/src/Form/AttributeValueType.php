<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AttributeValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('attribute', AttributeChoiceType::class, [
            'label' => 'attribute_values.form.attribute',
            'translation' => $options['translation'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('translation');
        $resolver->setAllowedTypes('translation', [Translation::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'attribute_value';
    }
}
