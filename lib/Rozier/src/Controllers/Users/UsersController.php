<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Users;

use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\Forms\AddUserType;
use Themes\Rozier\Forms\UserDetailsType;
use Themes\Rozier\Forms\UserType;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Utils\SessionListFilters;
use Twig\Error\RuntimeError;

class UsersController extends RozierApp
{
    /**
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            User::class,
            [],
            ['username' => 'ASC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('user_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['users'] = $listManager->getEntities();

        return $this->render('@RoadizRozier/users/list.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $userId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (
            !(
            $this->isGranted('ROLE_ACCESS_USERS') ||
            ($this->getUser() instanceof User && $this->getUser()->getId() == $userId)
            )
        ) {
            throw $this->createAccessDeniedException("You don't have access to this page: ROLE_ACCESS_USERS");
        }
        $user = $this->em()->find(User::class, $userId);
        if ($user === null) {
            throw new ResourceNotFoundException();
        }
        if (!$this->isGranted(Role::ROLE_SUPERADMIN) && $user->isSuperAdmin()) {
            throw $this->createAccessDeniedException("You cannot edit a super admin.");
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em()->flush();
            $msg = $this->getTranslator()->trans(
                'user.%name%.updated',
                ['%name%' => $user->getUsername()]
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'usersEditPage',
                ['userId' => $user->getId()]
            );
        }

        $this->assignation['user'] = $user;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/edit.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $userId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editDetailsAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (
            !(
                $this->isGranted('ROLE_ACCESS_USERS') ||
                ($this->getUser() instanceof User && $this->getUser()->getId() === $userId)
            )
        ) {
            throw $this->createAccessDeniedException("You don't have access to this page: ROLE_ACCESS_USERS");
        }
        $user = $this->em()->find(User::class, $userId);

        if ($user === null) {
            throw new ResourceNotFoundException();
        }
        if (!$this->isGranted(Role::ROLE_SUPERADMIN) && $user->isSuperAdmin()) {
            throw $this->createAccessDeniedException("You cannot edit a super admin.");
        }

        $form = $this->createForm(UserDetailsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * If pictureUrl is empty, use default Gravatar image.
             */
            if ($user->getPictureUrl() == '') {
                $user->setPictureUrl($user->getGravatarUrl());
            }

            $this->em()->flush();

            $msg = $this->getTranslator()->trans(
                'user.%name%.updated',
                ['%name%' => $user->getUsername()]
            );
            $this->publishConfirmMessage($request, $msg);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'usersEditDetailsPage',
                ['userId' => $user->getId()]
            );
        }

        $this->assignation['user'] = $user;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/editDetails.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        $user = new User();
        $user->sendCreationConfirmationEmail(true);
        $this->assignation['user'] = $user;

        $form = $this->createForm(AddUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em()->persist($user);
            $this->em()->flush();

            $msg = $this->getTranslator()->trans('user.%name%.created', ['%name%' => $user->getUsername()]);
            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('usersHomePage');
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/add.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $userId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS_DELETE');
        $user = $this->em()->find(User::class, (int) $userId);

        if ($user === null) {
            throw new ResourceNotFoundException();
        }

        if (!$this->isGranted(Role::ROLE_SUPERADMIN) && $user->isSuperAdmin()) {
            throw $this->createAccessDeniedException("You cannot edit a super admin.");
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em()->remove($user);
            $this->em()->flush();
            $msg = $this->getTranslator()->trans(
                'user.%name%.deleted',
                ['%name%' => $user->getUsername()]
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('usersHomePage');
        }

        $this->assignation['user'] = $user;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/users/delete.html.twig', $this->assignation);
    }
}
