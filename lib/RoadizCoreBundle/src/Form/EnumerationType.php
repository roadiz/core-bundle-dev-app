<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Group selector form field type.
 */
final class EnumerationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'strict' => true,
            'multiple' => false,
            'placeholder' => 'choose.value',
        ]);

        $resolver->setRequired(['nodeTypeField']);
        $resolver->setAllowedTypes('nodeTypeField', [NodeTypeField::class]);

        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $values = $options['nodeTypeField']->getDefaultValuesAsArray();

            foreach ($values as $value) {
                $value = trim($value);
                $choices[$value] = $value;
            }

            return $choices;
        });

        $resolver->setNormalizer('placeholder', function (Options $options, $placeholder) {
            if ('' !== $options['nodeTypeField']->getPlaceholder()) {
                $placeholder = $options['nodeTypeField']->getPlaceholder();
            }

            return $placeholder;
        });

        $resolver->setNormalizer('expanded', function (Options $options, $expanded) {
            return $options['nodeTypeField']->isExpanded();
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'enumeration';
    }
}
