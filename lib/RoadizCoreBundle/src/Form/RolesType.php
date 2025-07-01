<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RolesType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        #[Autowire(param: 'security.role_hierarchy.roles')]
        private readonly array $rolesHierarchy,
    ) {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'roles' => [],
            'multiple' => false,
        ]);

        $resolver->setAllowedTypes('multiple', ['bool']);
        $resolver->setAllowedTypes('roles', ['array']);

        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            foreach ($this->flattenRoles($this->rolesHierarchy) as $role) {
                if (
                    $this->security->isGranted($role)
                    && !in_array($role, $options['roles'])
                ) {
                    $choices[$role] = $role;
                }
            }

            ksort($choices);

            return $choices;
        });
    }

    private function flattenRoles(array $rolesHierarchy): array
    {
        $flattened = [];
        foreach ($rolesHierarchy as $role => $subRoles) {
            if (is_array($subRoles)) {
                $flattened = [
                    ...$flattened,
                    $role,
                    ...$this->flattenRoles($subRoles),
                ];
            } else {
                $flattened[] = $subRoles;
            }
        }

        return array_unique($flattened);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'roles';
    }
}
