<?php

declare(strict_types=1);

namespace App\Controller;

use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Dev-only helper to debug the session-bound CSRF token used by the legacy
 * /rz-admin/ajax write endpoints. Returns the current session id, a fresh valid
 * token, and whether a submitted token validates against this session.
 *
 * Hit it in the browser and from Bruno/curl with the same cookie: if the session
 * ids differ, the cookie you send does not carry the authenticated session.
 */
final class DebugCsrfController extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route(
        path: '/rz-admin/ajax/_debug/csrf',
        name: 'debugAjaxCsrf',
        methods: ['GET'],
        format: 'json',
        env: 'dev',
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $intention = AbstractAjaxController::AJAX_TOKEN_INTENTION;
        $submitted = $request->query->get('token');
        $session = $request->getSession();

        return new JsonResponse([
            'environment' => $this->getParameter('kernel.environment'),
            'sessionName' => $session->getName(),
            'sessionId' => $session->getId(),
            'authenticated' => null !== $this->getUser(),
            'user' => $this->getUser()?->getUserIdentifier(),
            'intention' => $intention,
            'submittedToken' => $submitted,
            'submittedTokenValid' => \is_string($submitted)
                && $this->csrfTokenManager->isTokenValid(new CsrfToken($intention, $submitted)),
            'freshToken' => $this->csrfTokenManager->getToken($intention)->getValue(),
        ]);
    }
}
