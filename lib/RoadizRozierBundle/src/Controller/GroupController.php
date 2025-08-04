<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\RolesType;
use RZ\Roadiz\CoreBundle\Form\UsersType;
use RZ\Roadiz\RozierBundle\Form\GroupType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Twig\Error\RuntimeError;

final class GroupController extends AbstractAdminController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Group;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'group';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Group();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/groups';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_GROUPS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Group::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return GroupType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'groupsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'groupsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Group) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function denyAccessUnlessItemGranted(PersistableInterface $item): void
    {
        $this->denyAccessUnlessGranted($item);
    }

    /**
     * Return an edition form for requested group.
     *
     * @throws RuntimeError
     */
    public function editRolesAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Group|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!$item instanceof Group) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $this->assignation['item'] = $item;
        $form = $this->buildEditRolesForm($item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->get('role')->getData();
            if (is_string($role)) {
                $item->setRoles([
                    ...$item->getRoles(),
                    $role,
                ]);
                $this->em()->flush();
                $msg = $this->translator->trans('role.%role%.linked_group.%group%', [
                    '%group%' => $item->getName(),
                    '%role%' => $role,
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $item);

                return $this->redirectToRoute(
                    'groupsEditRolesPage',
                    ['id' => $item->getId()]
                );
            }
            $form->get('role')->addError(new FormError('Role not found'));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/groups/roles.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function removeRolesAction(Request $request, int $id, string $role): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Group|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!($item instanceof Group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $this->assignation['item'] = $item;
        $this->assignation['role'] = $role;

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setRoles(
                array_values(
                    array_filter(
                        $item->getRoles(),
                        fn (string $existingRole) => $existingRole !== $role
                    )
                )
            );
            $this->em()->flush();
            $msg = $this->translator->trans('role.%role%.removed_from_group.%group%', [
                '%role%' => $role,
                '%group%' => $item->getName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $item);

            return $this->redirectToRoute(
                'groupsEditRolesPage',
                ['id' => $item->getId()]
            );
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/groups/removeRole.html.twig', $this->assignation);
    }

    public function editUsersAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Group|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!($item instanceof Group)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $this->assignation['item'] = $item;
        $form = $this->buildEditUsersForm($item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $user */
            $user = $this->em()->find(User::class, (int) $form->get('userId')->getData());

            if (null !== $user) {
                $user->addGroup($item);
                $this->em()->flush();
                $msg = $this->translator->trans('user.%user%.linked.group.%group%', [
                    '%group%' => $item->getName(),
                    '%user%' => $user->getUserName(),
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $user);

                return $this->redirectToRoute(
                    'groupsEditUsersPage',
                    ['id' => $item->getId()]
                );
            }
            $form->get('userId')->addError(new FormError('User not found'));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/groups/users.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function removeUsersAction(Request $request, int $id, int $userId): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Group|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);
        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);

        if (!($item instanceof Group)) {
            throw $this->createNotFoundException();
        }

        if (null === $user) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $this->assignation['item'] = $item;
        $this->assignation['user'] = $user;

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->removeGroup($item);
            $this->em()->flush();
            $msg = $this->translator->trans('user.%user%.removed_from_group.%group%', [
                '%user%' => $user->getUsername(),
                '%group%' => $item->getName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $user);

            return $this->redirectToRoute(
                'groupsEditUsersPage',
                [
                    'id' => $item->getId(),
                ]
            );
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/groups/removeUser.html.twig', $this->assignation);
    }

    private function buildEditRolesForm(Group $group): FormInterface
    {
        $builder = $this->createFormBuilder()
            ->add(
                'role',
                RolesType::class,
                [
                    'label' => 'choose.role',
                    'roles' => $group->getRoles(),
                ]
            )
        ;

        return $builder->getForm();
    }

    private function buildEditUsersForm(Group $group): FormInterface
    {
        $builder = $this->createFormBuilder()
            ->add(
                'userId',
                UsersType::class,
                [
                    'label' => 'choose.user',
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'users' => $group->getUsers(),
                ]
            )
        ;

        return $builder->getForm();
    }
}
