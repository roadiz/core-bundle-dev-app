<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TwoFactorCodeActivationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'twoFactorCode',
                'help' => 'twoFactorCode.help',
                'attr' => [
                    'autocomplete' => 'one-time-code',
                    'autofocus' => true,
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9]*',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6, 'max' => 8]),
                    new Regex(['pattern' => '/^[0-9]+$/']),
                ],
            ]);
    }
}
