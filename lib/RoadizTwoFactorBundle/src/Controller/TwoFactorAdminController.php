<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Controller;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Form\TwoFactorCodeActivationType;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\AuthenticatorTwoFactorProvider;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

final class TwoFactorAdminController extends RozierApp
{
    public function __construct(
        private TwoFactorUserProviderInterface $twoFactorUserProvider,
        private AuthenticatorTwoFactorProvider $authenticatorTwoFactorProvider,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function twoFactorAdminAction(Request $request, TokenStorageInterface $tokenStorage): Response
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
            $form = $this->createForm(FormType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->twoFactorUserProvider->createForUser($user);
                return $this->redirectToRoute('2fa_admin_two_factor');
            }
            $this->assignation['form'] = $form->createView();
        } elseif (!$twoFactorUser->isTotpAuthenticationEnabled()) {
            // Only display QR code if user has started 2FA activation
            $this->assignation['displayQrCodeTotp'] = $twoFactorUser instanceof TwoFactorInterface;
            $form = $this->createForm(TwoFactorCodeActivationType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /*
                 * Test if code is valid
                 */
                if (
                    $this->authenticatorTwoFactorProvider->validateAuthenticationCode(
                        $user,
                        $form->get('code')->getData()
                    )
                ) {
                    $this->twoFactorUserProvider->activate($twoFactorUser);
                    return $this->redirectToRoute('2fa_admin_two_factor');
                }

                $form->get('code')->addError(new FormError('invalid_totp_code'));
            }
            $this->assignation['totpForm'] = $form->createView();
        }

        return $this->render('@RoadizTwoFactor/admin/two_factor.html.twig', $this->assignation);
    }

    public function twoFactorDisableAction(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $user = $tokenStorage->getToken()->getUser();
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }
        $twoFactorUser = $this->twoFactorUserProvider->getFromUser($user);

        if (!$twoFactorUser instanceof TwoFactorUser) {
            return $this->redirectToRoute('2fa_admin_two_factor');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->twoFactorUserProvider->disable($twoFactorUser);
            return $this->redirectToRoute('2fa_admin_two_factor');
        }
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizTwoFactor/admin/disable_two_factor.html.twig', $this->assignation);
    }
}
