<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class NodeSourceBaseType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'title',
            'empty_data' => '',
            'required' => false,
            'attr' => [
                'data-dev-name' => '{{ nodeSource.'.StringHandler::camelCase('title').' }}',
                'lang' => \mb_strtolower(str_replace('_', '-', $options['translation']->getLocale())),
                'dir' => $options['translation']->isRtl() ? 'rtl' : 'ltr',
            ],
            'constraints' => [
                new Length([
                    'max' => 255,
                ]),
            ],
        ]);

        if (true === $options['publishable']) {
            $builder->add('publishedAt', DateTimeType::class, [
                'label' => 'publishedAt',
                'required' => false,
                'attr' => [
                    'data-dev-name' => '{{ nodeSource.'.StringHandler::camelCase('publishedAt').' }}',
                ],
                'html5' => true,
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ]);
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'nodesourcebase';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'inherit_data' => true,
            'publishable' => false,
            'attr' => [
                'no-field-group' => true,
            ],
        ]);

        $resolver->setRequired('translation');

        $resolver->setAllowedTypes('publishable', 'boolean');
        $resolver->setAllowedTypes('translation', Translation::class);
    }
}
