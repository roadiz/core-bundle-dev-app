<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\User;

use Doctrine\Persistence\ManagerRegistry;
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
final class UserRoleController extends AbstractController
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
            /** @var string|null $role */
            $role = $form->get('role')->getData();

            if (is_string($role)) {
                $user->setUserRoles([
                    ...$user->getUserRoles(),
                    $role,
                ]);
                $this->managerRegistry->getManager()->flush();

                $msg = $this->translator->trans('user.%user%.role.%role%.linked', [
                    '%user%' => $user->getUserName(),
                    '%role%' => $role,
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

    public function removeRoleAction(Request $request, int $userId, string $role): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);
        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        if (!$this->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUserRoles(array_filter(
                $user->getUserRoles(),
                fn (string $userRole) => $userRole !== $role
            ));
            $this->managerRegistry->getManager()->flush();
            $msg = $this->translator->trans(
                'user.%name%.role_removed',
                ['%name%' => $role]
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
                'role',
                RolesType::class,
                [
                    'label' => 'choose.role',
                    'roles' => $user->getUserRoles(),
                ]
            )
        ;

        return $builder->getForm();
    }
}
