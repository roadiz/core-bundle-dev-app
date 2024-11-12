<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Group selector form field type.
 */
class GroupsType extends AbstractType
{
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected ManagerRegistry $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->managerRegistry = $managerRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(function ($modelToForm) {
            if (null !== $modelToForm) {
                if ($modelToForm instanceof Collection) {
                    $modelToForm = $modelToForm->toArray();
                }

                return array_map(function (Group $group) {
                    return $group->getId();
                }, $modelToForm);
            }

            return null;
        }, function ($formToModels) {
            if (null === $formToModels || (is_array($formToModels) && 0 === count($formToModels))) {
                return [];
            }

            return $this->managerRegistry->getRepository(Group::class)->findBy([
                'id' => $formToModels,
            ]);
        }));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);

        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $groups = $this->managerRegistry->getRepository(Group::class)->findAll();

            /** @var Group $group */
            foreach ($groups as $group) {
                if ($this->authorizationChecker->isGranted($group)) {
                    $choices[$group->getName()] = $group->getId();
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
        return 'groups';
    }
}
