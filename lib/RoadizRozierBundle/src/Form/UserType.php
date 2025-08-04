<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\CreatePasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email',
                'empty_data' => '',
            ])
            ->add('username', TextType::class, [
                'label' => 'username',
                'empty_data' => '',
            ])
            ->add('plainPassword', CreatePasswordType::class, [
                'invalid_message' => 'password.must.match',
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'user.backoffice.language',
                'required' => false,
                'choices' => [
                    'English' => 'en',
                    'Français' => 'fr',
                ],
                'placeholder' => 'use.website.default_language',
            ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'user';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'label' => false,
            'email' => '',
            'username' => '',
            'data_class' => User::class,
            'attr' => [
                'class' => 'uk-form user-form',
            ],
        ]);
    }
}
