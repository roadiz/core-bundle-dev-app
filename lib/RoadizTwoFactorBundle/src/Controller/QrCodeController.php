<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Controller;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class QrCodeController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorUserProviderInterface $twoFactorUserProvider,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly GoogleAuthenticatorInterface $googleAuthenticator
    ) {
    }

    public function totpQrCodeAction(TokenStorageInterface $tokenStorage): Response
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

        if (!($twoFactorUser instanceof TwoFactorInterface)) {
            throw $this->createNotFoundException('Cannot display QR code');
        }

        if ($user instanceof GoogleAuthenticatorTwoFactorInterface) {
            $qrCodeContent = $this->googleAuthenticator->getQrContent($twoFactorUser);
        } else {
            $qrCodeContent = $this->totpAuthenticator->getQrContent($twoFactorUser);
        }

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(512)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }
}
