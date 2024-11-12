<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Theme selector form field type.
 */
class ThemesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [],
        ]);
        $resolver->setRequired('themes_config');
        $resolver->setAllowedTypes('themes_config', 'array');
        $resolver->setNormalizer('choices', function (Options $options, $value) {
            $value = [];
            foreach ($options['themes_config'] as $themeConfig) {
                $class = $themeConfig['classname'];
                /** @var callable $callable */
                $callable = [$class, 'getThemeName'];
                $value[call_user_func($callable)] = $class;
            }

            return $value;
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'classname';
    }
}
