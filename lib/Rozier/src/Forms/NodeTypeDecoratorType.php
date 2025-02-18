<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeTypeDecoratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', TextType::class, [
                'label' => 'nodeTypeDecorator.path',
            ])
            ->add('property', EnumType::class, [
                'class' => NodeTypeDecoratorProperty::class,
                'choice_label' => fn (NodeTypeDecoratorProperty $property) => $property->value,
                'label' => 'nodeTypeDecorator.property',
                'required' => true,
            ])
            ->add('value', TextType::class, [
                'label' => 'nodeTypeDecorator.value',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'nodetypedecorator';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'name' => '',
            'data_class' => NodeTypeDecorator::class,
            'attr' => [
                'class' => 'uk-form node-type-form',
            ],
        ]);
    }
}
