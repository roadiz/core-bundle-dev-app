<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\LoginRequestForm;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\CoreBundle\Traits\LoginRequestTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LoginRequestController extends AbstractController
{
    use LoginRequestTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserViewer $userViewer,
        private readonly ManagerRegistry $managerRegistry,
        private readonly RateLimiterFactoryInterface $loginRequestLimiter,
        private readonly RateLimiterFactoryInterface $loginRequestEmailLimiter,
    ) {
    }

    #[\Override]
    protected function getUserViewer(): UserViewer
    {
        return $this->userViewer;
    }

    #[Route(
        path: '/rz-admin/login/request',
        name: 'loginRequestPage',
        methods: ['GET', 'POST'],
    )]
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(LoginRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $limit = $this->loginRequestLimiter->create($request->getClientIp())->consume();
            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
            }

            // Per-IP limiting alone lets an IP-rotating attacker flood a single
            // victim's mailbox: also cap requests per targeted email.
            $email = $form->get('email')->getData();
            if (\is_string($email) && '' !== $email) {
                $emailLimit = $this->loginRequestEmailLimiter->create(mb_strtolower(trim($email)))->consume();
                if (false === $emailLimit->isAccepted()) {
                    throw new TooManyRequestsHttpException($emailLimit->getRetryAfter()->getTimestamp() - time());
                }
            }

            if ($form->isValid()) {
                $this->sendConfirmationEmail(
                    $form,
                    $this->managerRegistry->getManagerForClass(User::class) ?? throw new \RuntimeException('No entity manager found for User class.'),
                    $this->logger,
                    $this->urlGenerator
                );
            }

            /*
             * Always go to confirm even if email is not valid
             * for avoiding database sniffing.
             */
            return $this->redirectToRoute(
                'loginRequestConfirmPage'
            );
        }

        return $this->render('@RoadizRozier/login/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/rz-admin/login/request/confirm',
        name: 'loginRequestConfirmPage',
        methods: ['GET'],
    )]
    public function confirmAction(): Response
    {
        return $this->render('@RoadizRozier/login/requestConfirm.html.twig');
    }
}
