<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Users;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\RolesType;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class UsersRolesController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function editRolesAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);

        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildEditRolesForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Role|null $role */
            $role = $this->managerRegistry
                ->getRepository(Role::class)
                ->find($form->get('roleId')->getData());

            if (null !== $role) {
                $user->addRoleEntity($role);
                $this->managerRegistry->getManager()->flush();

                $msg = $this->translator->trans('user.%user%.role.%role%.linked', [
                    '%user%' => $user->getUserName(),
                    '%role%' => $role->getRole(),
                ]);

                $this->logTrail->publishConfirmMessage($request, $msg, $user);

                return $this->redirectToRoute(
                    'usersEditRolesPage',
                    ['userId' => $user->getId()]
                );
            }
            $form->get('roleId')->addError(new FormError('Role not found'));
        }

        return $this->render('@RoadizRozier/users/roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    public function removeRoleAction(Request $request, int $userId, int $roleId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);
        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        /** @var Role|null $role */
        $role = $this->managerRegistry->getRepository(Role::class)->find($roleId);
        if (null === $role) {
            throw new ResourceNotFoundException();
        }

        if (!$this->isGranted($role->getRole())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->removeRoleEntity($role);
            $this->managerRegistry->getManager()->flush();
            $msg = $this->translator->trans(
                'user.%name%.role_removed',
                ['%name%' => $role->getRole()]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $user);

            return $this->redirectToRoute(
                'usersEditRolesPage',
                ['userId' => $user->getId()]
            );
        }

        return $this->render('@RoadizRozier/users/removeRole.html.twig', [
            'user' => $user,
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    private function buildEditRolesForm(User $user): FormInterface
    {
        $builder = $this->createFormBuilder()
            ->add(
                'roleId',
                RolesType::class,
                [
                    'label' => 'choose.role',
                    'roles' => $user->getRolesEntities(),
                ]
            )
        ;

        return $builder->getForm();
    }
}
