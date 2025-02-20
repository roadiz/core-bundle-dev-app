<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RolesType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'roles' => new ArrayCollection(),
            'multiple' => false,
        ]);

        $resolver->setAllowedTypes('multiple', ['bool']);
        $resolver->setAllowedTypes('roles', [Collection::class]);

        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $roles = $this->managerRegistry->getRepository(Role::class)->findAll();

            /** @var Role $role */
            foreach ($roles as $role) {
                if (
                    $this->authorizationChecker->isGranted($role->getRole())
                    && !$options['roles']->contains($role)
                ) {
                    $choices[$role->getRole()] = $role->getId();
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
        return 'roles';
    }
}
