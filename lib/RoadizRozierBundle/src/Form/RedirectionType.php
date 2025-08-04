<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RedirectionType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('query', TextType::class, [
            'label' => (!$options['only_query']) ? 'redirection.query' : false,
            'attr' => [
                'placeholder' => $options['placeholder'],
            ],
            'empty_data' => '',
        ]);
        if (false === $options['only_query']) {
            $builder->add('redirectUri', TextareaType::class, [
                'label' => 'redirection.redirect_uri',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'redirection.type',
                'choices' => [
                    'redirection.moved_permanently' => Response::HTTP_MOVED_PERMANENTLY,
                    'redirection.moved_temporarily' => Response::HTTP_FOUND,
                ],
            ]);
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'redirection';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Redirection::class,
            'only_query' => false,
            'placeholder' => null,
            'attr' => [
                'class' => 'uk-form redirection-form',
            ],
        ]);
    }
}
