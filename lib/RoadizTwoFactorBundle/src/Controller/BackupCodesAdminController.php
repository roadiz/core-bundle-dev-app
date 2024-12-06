<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Controller;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class BackupCodesAdminController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorUserProviderInterface $twoFactorUserProvider,
    ) {
    }

    public function backupCodesAdminAction(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if ($this->isGranted('IS_IMPERSONATOR')) {
            throw $this->createAccessDeniedException('You cannot impersonate to access this page.');
        }

        $user = $tokenStorage->getToken()->getUser();
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }
        $twoFactorUser = $this->twoFactorUserProvider->getFromUser($user);
        if (!$twoFactorUser instanceof TwoFactorUser) {
            throw $this->createNotFoundException('User does not have 2FA enabled.');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        $assignation = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $backupCodes = $this->twoFactorUserProvider->generateBackupCodes($twoFactorUser);
            $assignation['backupCodes'] = $backupCodes;
        }
        $assignation['form'] = $form->createView();

        return $this->render('@RoadizTwoFactor/admin/backup_codes.html.twig', $assignation);
    }
}
