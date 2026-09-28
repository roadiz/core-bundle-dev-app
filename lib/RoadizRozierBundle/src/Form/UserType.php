<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\CreatePasswordType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

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
            ]);

        $user = $options['data'] ?? null;
        if ($user instanceof User && $this->isOwnUser($user)) {
            // Changing own password requires current password, even with SSO sessions.
            // Keep the stored hash now: User::setPlainPassword() overwrites it before validation.
            $currentHash = $user->getPassword();
            $builder->add('currentPassword', PasswordType::class, [
                'label' => 'current.password',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Callback(function (?string $value, ExecutionContextInterface $context) use ($user, $currentHash): void {
                        $root = $context->getRoot();
                        if (!$root instanceof FormInterface || empty($root->get('plainPassword')->getData())) {
                            return;
                        }
                        if (empty($value) || !$this->passwordHasherFactory->getPasswordHasher($user)->verify($currentHash, $value)) {
                            $context->buildViolation('current.password.invalid')->addViolation();
                        }
                    }),
                ],
            ]);
        }

        $builder
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

    private function isOwnUser(User $user): bool
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof UserInterface || null === $user->getId()) {
            return false;
        }
        $identifier = $currentUser->getUserIdentifier();

        // OpenID accounts are identified by email
        return $identifier === $user->getUsername() || $identifier === $user->getEmail();
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
                'class' => 'rz-form user-form',
            ],
        ]);
    }
}
