<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

final class LoginRequestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'required' => true,
            'label' => 'your.account.email',
            'constraints' => [
                new Email([
                    'message' => 'email.invalid',
                ]),
                new ValidAccountEmail(),
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'login_request';
    }
}
