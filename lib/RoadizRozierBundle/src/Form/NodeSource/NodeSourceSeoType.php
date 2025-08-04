<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class NodeSourceSeoType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('metaTitle', TextType::class, [
            'label' => 'metaTitle',
            'help' => 'nodeSource.metaTitle.help',
            'required' => false,
            'attr' => [
                'data-max-length' => 80,
            ],
            'constraints' => [
                new Length([
                    'max' => 80,
                ]),
            ],
        ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'metaDescription',
                'help' => 'nodeSource.metaDescription.help',
                'required' => false,
            ])
            ->add('noIndex', CheckboxType::class, [
                'label' => 'nodeSource.noIndex',
                'help' => 'nodeSource.noIndex.help',
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => NodesSources::class,
            'property' => 'id',
        ]);
    }
}
