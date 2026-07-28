<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class TranstypeType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'nodeTypeName',
            NodeTypesType::class,
            [
                'currentType' => $options['currentType'],
                'showInvisible' => true,
                'label' => 'nodeType',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]
        );
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'transtype';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'label' => false,
            'nodeName' => null,
            'attr' => [
                'class' => 'rz-form transtype-form',
            ],
        ]);

        $resolver->setRequired([
            'currentType',
        ]);
        $resolver->setAllowedTypes('currentType', NodeType::class);
    }
}
