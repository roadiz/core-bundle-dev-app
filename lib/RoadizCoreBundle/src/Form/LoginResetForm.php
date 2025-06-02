<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountConfirmationToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LoginResetForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('token', HiddenType::class, [
            'required' => true,
            'data' => $options['token'],
            'label' => false,
            'constraints' => [
                new ValidAccountConfirmationToken([
                    'ttl' => $options['confirmationTtl'],
                    'message' => 'confirmation.token.is.invalid',
                    'expiredMessage' => 'confirmation.token.has.expired',
                ]),
            ],
        ])
        ->add('plainPassword', CreatePasswordType::class, [
            'required' => true,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'login_reset';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'token',
            'confirmationTtl',
        ]);

        $resolver->setAllowedTypes('token', ['string']);
        $resolver->setAllowedTypes('confirmationTtl', ['int']);
    }
}
