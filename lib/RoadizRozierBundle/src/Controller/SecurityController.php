<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\OpenId\Exception\DiscoveryNotAvailableException;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Themes\Rozier\RozierServiceRegistry;

class SecurityController extends AbstractController
{
    private OAuth2LinkGenerator $oAuth2LinkGenerator;
    private LoggerInterface $logger;
    private Settings $settingsBag;
    private RozierServiceRegistry $rozierServiceRegistry;

    public function __construct(
        OAuth2LinkGenerator $oAuth2LinkGenerator,
        LoggerInterface $logger,
        Settings $settingsBag,
        RozierServiceRegistry $rozierServiceRegistry
    ) {
        $this->oAuth2LinkGenerator = $oAuth2LinkGenerator;
        $this->logger = $logger;
        $this->settingsBag = $settingsBag;
        $this->rozierServiceRegistry = $rozierServiceRegistry;
    }

    #[Route(path: '/rz-admin/login', name: 'roadiz_rozier_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('adminHomePage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $assignation = [
            'last_username' => $lastUsername,
            'error' => $error,
            'themeServices' => $this->rozierServiceRegistry
        ];

        try {
            if ($this->oAuth2LinkGenerator->isSupported($request)) {
                $assignation['openid_button_label'] = $this->settingsBag->get('openid_button_label');
                $assignation['openid'] = $this->oAuth2LinkGenerator->generate(
                    $request,
                    $this->generateUrl('loginPage', [], UrlGeneratorInterface::ABSOLUTE_URL)
                );
            }
        } catch (DiscoveryNotAvailableException $exception) {
            $this->logger->notice($exception->getMessage());
        }

        return $this->render('@RoadizRozier/security/login.html.twig', $assignation);
    }

    #[Route(path: '/rz-admin/logout', name: 'roadiz_rozier_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
