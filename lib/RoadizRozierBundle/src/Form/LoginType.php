<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class LoginType extends AbstractType
{
    public function __construct(protected UrlGeneratorInterface $urlGenerator, protected RequestStack $requestStack)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('_username', TextType::class, [
            'label' => 'username',
            'attr' => [
                'autocomplete' => 'username',
            ],
            'constraints' => [
                new NotNull(),
                new NotBlank(),
            ],
        ])
        ->add('_password', PasswordType::class, [
            'label' => 'password',
            'attr' => [
                'autocomplete' => 'current-password',
            ],
            'constraints' => [
                new NotNull(),
                new NotBlank(),
            ],
        ])
        ->add('_remember_me', CheckboxType::class, [
            'label' => 'keep_me_logged_in',
            'required' => false,
            'attr' => [
                'checked' => true,
            ],
        ]);

        if ($this->requestStack->getMainRequest()?->query->has('_home')) {
            $builder->add('_target_path', HiddenType::class, [
                'data' => $this->urlGenerator->generate('adminHomePage'),
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('action', fn (Options $options) => $this->urlGenerator->generate('loginCheckPage'));
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        /*
         * No prefix for firewall to catch username and password from request.
         */
        return '';
    }
}
