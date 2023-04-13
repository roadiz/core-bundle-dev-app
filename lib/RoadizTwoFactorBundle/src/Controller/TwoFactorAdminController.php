<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class TwoFactorAdminController extends RozierApp
{
    public function __construct(
        private ManagerRegistry $managerRegistry
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function twoFactorAdminAction(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $user = $tokenStorage->getToken()->getUser();
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }
        $twoFactorUser = $this->managerRegistry
            ->getRepository(TwoFactorUser::class)
            ->findOneBy(['user' => $user]);

        if (!$twoFactorUser instanceof TwoFactorUser) {
            $twoFactorUser = new TwoFactorUser();
            $twoFactorUser->setUser($user);

            $form = $this->createForm(FormType::class, $twoFactorUser);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $twoFactorUser->setSecret(base64_encode((new TokenGenerator())->generateToken()));
                $this->managerRegistry->getManager()->persist($twoFactorUser);
                $this->managerRegistry->getManager()->flush();
                return $this->redirectToRoute('2fa_admin_two_factor');
            }
            $this->assignation['form'] = $form->createView();
        }

        $this->assignation['displayQrCodeTotp'] = $twoFactorUser instanceof TwoFactorInterface && $twoFactorUser->isTotpAuthenticationEnabled();

        return $this->render('@RoadizTwoFactor/admin/user.html.twig', $this->assignation);
    }
}
