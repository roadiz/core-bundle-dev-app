<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RoleEntityType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
    ) {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => false,
            'choice_label' => fn (?Role $choice) => $choice ? $choice->getRole() : '',
            'choice_value' => fn (?Role $choice) => $choice ? $choice->getId() : '',
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $roles = $this->managerRegistry->getRepository(Role::class)->findAll();

            /** @var Role $role */
            foreach ($roles as $role) {
                if ($this->security->isGranted($role->getRole())) {
                    $choices[] = $role;
                }
            }

            return $choices;
        });
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'role_entity';
    }
}
