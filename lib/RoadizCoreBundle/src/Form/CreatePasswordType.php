<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Rollerworks\Component\PasswordCommonList\Constraints\NotInPasswordCommonList;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreatePasswordType extends RepeatedType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'type' => PasswordType::class,
            'invalid_message' => 'password.must.match',
            'options' => [
                'constraints' => [
                    new NotInPasswordCommonList(),
                ],
            ],
            'first_options' => [
                'label' => 'choose.a.new.password',
            ],
            'second_options' => [
                'label' => 'passwordVerify',
            ],
            'required' => false,
            'error_mapping' => function (Options $options) {
                return ['.' => $options['first_name']];
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'repeated';
    }
}
