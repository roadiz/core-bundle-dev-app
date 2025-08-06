<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\User;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\UserSecurityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class UserSecurityController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function securityAction(Request $request, int $userId): Response
    {
        // Only user managers can review security
        $this->denyAccessUnlessGranted('ROLE_ACCESS_USERS');
        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);

        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(UserSecurityType::class, $user, [
            'canChroot' => $this->isGranted('ROLE_SUPERADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();
            $msg = $this->translator->trans(
                'user.%name%.security.updated',
                ['%name%' => $user->getUsername()]
            );

            $this->logTrail->publishConfirmMessage($request, $msg, $user);

            return $this->redirectToRoute(
                'usersSecurityPage',
                ['userId' => $user->getId()]
            );
        }

        return $this->render('@RoadizRozier/users/security.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
