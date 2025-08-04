<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\User;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\User\UserJoinedGroupEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserLeavedGroupEvent;
use RZ\Roadiz\CoreBundle\Form\GroupsType;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class UserGroupController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function editGroupsAction(Request $request, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);
        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildEditGroupsForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['userId'] == $user->getId()) {
                if (array_key_exists('group', $data) && $data['group'][0] instanceof Group) {
                    $group = $data['group'][0];
                } elseif (array_key_exists('group', $data) && is_numeric($data['group'])) {
                    $group = $this->managerRegistry->getRepository(Group::class)->find($data['group']);
                } else {
                    $group = null;
                }

                if (null !== $group) {
                    $user->addGroup($group);
                    $this->managerRegistry->getManager()->flush();
                    $this->eventDispatcher->dispatch(new UserJoinedGroupEvent($user, $group));

                    $msg = $this->translator->trans('user.%user%.group.%group%.linked', [
                        '%user%' => $user->getUserName(),
                        '%group%' => $group->getName(),
                    ]);
                    $this->logTrail->publishConfirmMessage($request, $msg, $user);

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

        return $this->render('@RoadizRozier/users/groups.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    public function removeGroupAction(Request $request, int $userId, int $groupId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);
        /** @var Group|null $group */
        $group = $this->managerRegistry->getRepository(Group::class)->find($groupId);

        if (null === $user) {
            throw new ResourceNotFoundException();
        }
        if (null === $group) {
            throw new ResourceNotFoundException();
        }

        if (!$this->isGranted($group)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->removeGroup($group);
            $this->managerRegistry->getManager()->flush();

            $this->eventDispatcher->dispatch(new UserLeavedGroupEvent($user, $group));

            $msg = $this->translator->trans('user.%user%.group.%group%.removed', [
                '%user%' => $user->getUserName(),
                '%group%' => $group->getName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $user);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'usersEditGroupsPage',
                ['userId' => $user->getId()]
            );
        }

        return $this->render('@RoadizRozier/users/removeGroup.html.twig', [
            'user' => $user,
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

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
                    'label' => 'Group',
                ]
            )
        ;

        return $builder->getForm();
    }
}
