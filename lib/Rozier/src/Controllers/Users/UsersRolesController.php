<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Users;

use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\RolesType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class UsersRolesController extends RozierApp
{
    /**
     * @param Request $request
     * @param int $userId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editRolesAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);

        if ($user === null) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['user'] = $user;
        $form = $this->buildEditRolesForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Role|null $role */
            $role = $this->em()->find(Role::class, $form->get('roleId')->getData());

            if (null !== $role) {
                $user->addRoleEntity($role);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans('user.%user%.role.%role%.linked', [
                    '%user%' => $user->getUserName(),
                    '%role%' => $role->getRole(),
                ]);

                $this->publishConfirmMessage($request, $msg, $user);

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute(
                    'usersEditRolesPage',
                    ['userId' => $user->getId()]
                );
            }
            $form->get('roleId')->addError(new FormError('Role not found'));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/roles.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested role depending on the user.
     *
     * @param Request $request
     * @param int $userId
     * @param int $roleId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function removeRoleAction(Request $request, int $userId, int $roleId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);
        if ($user === null) {
            throw new ResourceNotFoundException();
        }

        /** @var Role|null $role */
        $role = $this->em()->find(Role::class, $roleId);
        if ($role === null) {
            throw new ResourceNotFoundException();
        }

        if (!$this->isGranted($role->getRole())) {
            throw $this->createAccessDeniedException();
        }

        $this->assignation['user'] = $user;
        $this->assignation['role'] = $role;

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->removeRoleEntity($role);
            $this->em()->flush();
            $msg = $this->getTranslator()->trans(
                'user.%name%.role_removed',
                ['%name%' => $role->getRole()]
            );
            $this->publishConfirmMessage($request, $msg, $user);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'usersEditRolesPage',
                ['userId' => $user->getId()]
            );
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/removeRole.html.twig', $this->assignation);
    }

    /**
     * @param User $user
     *
     * @return FormInterface
     */
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
