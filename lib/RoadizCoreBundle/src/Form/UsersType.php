<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'users' => new ArrayCollection(),
        ]);
        $resolver->setAllowedTypes('users', [Collection::class]);

        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $users = $this->managerRegistry->getRepository(User::class)->findAll();

            /** @var User $user */
            foreach ($users as $user) {
                if (!$options['users']->contains($user)) {
                    $choices[$user->getUserName()] = $user->getId();
                }
            }

            return $choices;
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'users';
    }
}
