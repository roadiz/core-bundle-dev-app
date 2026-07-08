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
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
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
                new Length(max: 80),
            ],
        ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'metaDescription',
                'help' => 'nodeSource.metaDescription.help',
                'required' => false,
                'attr' => [
                    'data-max-length' => 160,
                ],
                'constraints' => [
                    /*
                     * Either leave it empty (a "metaDescriptionFallback" field
                     * then feeds the head) or write a relevant description: a
                     * too-short meta-description is discarded at exposition
                     * (see NodesSourcesHead), so reject it at input time
                     * instead of silently dropping the editor's text.
                     */
                    new AtLeastOneOf(
                        constraints: [
                            new Blank(),
                            // NodesSourcesHead discards candidates of 20 chars or
                            // fewer (mb_strlen > 20), so require strictly more.
                            new Length(min: 21),
                        ],
                        message: 'A meta-description should either be left empty or be longer than 20 characters to stay relevant for SEO.',
                        includeInternalMessages: false,
                    ),
                ],
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
