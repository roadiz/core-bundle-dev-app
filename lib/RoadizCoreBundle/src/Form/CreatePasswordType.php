<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class CreatePasswordType extends RepeatedType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'type' => PasswordType::class,
            'invalid_message' => 'password.must.match',
            'options' => [
                'constraints' => [
                    new Assert\Length(min: 8, max: 120),
                    new Assert\NotCompromisedPassword(),
                    new Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM),
                ],
            ],
            'first_options' => [
                'label' => 'choose.a.new.password',
            ],
            'second_options' => [
                'label' => 'passwordVerify',
            ],
            'required' => false,
            'error_mapping' => fn (Options $options) => ['.' => $options['first_name']],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'repeated';
    }
}
