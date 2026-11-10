<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use RZ\Roadiz\OpenId\Exception\DiscoveryNotAvailableException;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly OAuth2LinkGenerator $oAuth2LinkGenerator,
        private readonly LoggerInterface $logger,
        private readonly Settings $settingsBag,
        private readonly LoginLinkSenderInterface $loginLinkSender,
    ) {
    }

    #[Route(path: '/rz-admin/login', name: 'loginPage')]
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

    #[Route('/rz-admin/login_link', name: 'roadiz_rozier_login_link')]
    public function requestLoginLink(
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        RateLimiterFactoryInterface $loginLinkRequestLimiter,
        RateLimiterFactoryInterface $loginLinkRequestEmailLimiter,
    ): Response {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            $limit = $loginLinkRequestLimiter->create($request->getClientIp())->consume();
            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
            }

            // load the user in some way (e.g. using the form input)
            $email = $request->getPayload()->get('email');

            // Per-IP limiting alone lets an IP-rotating attacker flood a single
            // victim's mailbox: also cap requests per targeted email.
            if (\is_string($email) && '' !== $email) {
                $emailLimit = $loginLinkRequestEmailLimiter->create(mb_strtolower(trim($email)))->consume();
                if (false === $emailLimit->isAccepted()) {
                    throw new TooManyRequestsHttpException($emailLimit->getRetryAfter()->getTimestamp() - time());
                }
            }

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user instanceof UserInterface) {
                // Do not reveal whether a user account exists or not
                return $this->redirectToRoute('roadiz_rozier_login_link_sent');
            }
            // create a login link for $user this returns an instance
            // of LoginLinkDetails
            $loginLinkDetails = $loginLinkHandler->createLoginLink($user, $request);
            $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);

            return $this->redirectToRoute('roadiz_rozier_login_link_sent');
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('@RoadizRozier/security/request_login_link.html.twig');
    }

    #[Route('/rz-admin/login_link_sent', name: 'roadiz_rozier_login_link_sent')]
    public function loginLinkSent(): Response
    {
        return $this->render('@RoadizRozier/security/login_link_sent.html.twig');
    }

    #[Route(path: '/rz-admin/logout', name: 'logoutPage')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Reached only on a plain GET click-through of the emailed login link: `check_post_only`
     * on the `login_link` firewall config means the authenticator ignores GET requests here,
     * so this renders an auto-submitting POST form instead (defeats mail-scanner link prefetch,
     * which never executes the JS or clicks the fallback button).
     */
    #[Route('/rz-admin/login_link_check', name: 'login_link_check')]
    public function check(Request $request): Response
    {
        return $this->render('@RoadizRozier/security/login_link_confirm.html.twig', [
            'confirm_url' => $request->getRequestUri(),
        ]);
    }
}
