<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Users;

use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\User\UserJoinedGroupEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserLeavedGroupEvent;
use RZ\Roadiz\CoreBundle\Form\GroupsType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Themes\Rozier\RozierApp;

class UsersGroupsController extends RozierApp
{
    public function editGroupsAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);

        if ($user !== null) {
            $this->assignation['user'] = $user;

            $form = $this->buildEditGroupsForm($user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                if ($data['userId'] == $user->getId()) {
                    if (array_key_exists('group', $data) && $data['group'][0] instanceof Group) {
                        $group = $data['group'][0];
                    } elseif (array_key_exists('group', $data) && is_numeric($data['group'])) {
                        $group = $this->em()->find(Group::class, $data['group']);
                    } else {
                        $group = null;
                    }

                    if ($group !== null) {
                        $user->addGroup($group);
                        $this->em()->flush();

                        $this->dispatchEvent(new UserJoinedGroupEvent($user, $group));

                        $msg = $this->getTranslator()->trans('user.%user%.group.%group%.linked', [
                            '%user%' => $user->getUserName(),
                            '%group%' => $group->getName(),
                        ]);
                        $this->publishConfirmMessage($request, $msg, $user);

                        /*
                         * Force redirect to avoid resending form when refreshing page
                         */
                        return $this->redirectToRoute(
                            'usersEditGroupsPage',
                            ['userId' => $user->getId()]
                        );
                    }
                }
            }

            $this->assignation['form'] = $form->createView();

            return $this->render('@RoadizRozier/users/groups.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }

    public function removeGroupAction(Request $request, int $userId, int $groupId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);
        /** @var Group|null $group */
        $group = $this->em()->find(Group::class, $groupId);

        if (!$this->isGranted($group)) {
            throw $this->createAccessDeniedException();
        }

        if ($user !== null) {
            $this->assignation['user'] = $user;
            $this->assignation['group'] = $group;

            $form = $this->createForm(FormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->removeGroup($group);
                $this->em()->flush();

                $this->dispatchEvent(new UserLeavedGroupEvent($user, $group));

                $msg = $this->getTranslator()->trans('user.%user%.group.%group%.removed', [
                    '%user%' => $user->getUserName(),
                    '%group%' => $group->getName(),
                ]);
                $this->publishConfirmMessage($request, $msg, $user);

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute(
                    'usersEditGroupsPage',
                    ['userId' => $user->getId()]
                );
            }

            $this->assignation['form'] = $form->createView();

            return $this->render('@RoadizRozier/users/removeGroup.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param User $user
     *
     * @return FormInterface
     */
    private function buildEditGroupsForm(User $user): FormInterface
    {
        $defaults = [
            'userId' => $user->getId(),
        ];
        $builder = $this->createFormBuilder($defaults)
            ->add(
                'userId',
                HiddenType::class,
                [
                    'data' => $user->getId(),
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                ]
            )
            ->add(
                'group',
                GroupsType::class,
                [
                    'label' => 'Group'
                ]
            )
        ;

        return $builder->getForm();
    }
}
