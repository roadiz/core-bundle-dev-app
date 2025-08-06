<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\User\UserProvider;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\UserBundle\Api\Dto\UserPasswordRequestInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Notifier\UserPasswordRequestNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Process a user identifier into a password request.
 */
final readonly class UserPasswordRequestProcessor implements ProcessorInterface
{
    use CaptchaProtectedTrait;

    public function __construct(
        private LoggerInterface $logger,
        private RateLimiterFactoryInterface $passwordRequestLimiter,
        private ManagerRegistry $managerRegistry,
        private RequestStack $requestStack,
        private UserProvider $userProvider,
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private CaptchaServiceInterface $recaptchaService,
        private string $passwordResetUrl,
    ) {
    }

    #[\Override]
    protected function getCaptchaService(): CaptchaServiceInterface
    {
        return $this->recaptchaService;
    }

    #[\Override]
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof UserPasswordRequestInput) {
            throw new \RuntimeException(sprintf('Cannot process %s', $data::class));
        }
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new \RuntimeException('Cannot process password request without a request.');
        }
        $limiter = $this->passwordRequestLimiter->create($request->getClientIp());
        $limit = $limiter->consume();
        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
        }

        $this->validateCaptchaHeader($request);

        $user = $this->getUser($data->identifier);

        if (!$user instanceof User) {
            // Do not throw an exception to avoid user enumeration
            return new VoidOutput();
        }

        try {
            $tokenGenerator = new TokenGenerator($this->logger);
            $user->setPasswordRequestedAt(new \DateTime());
            $user->setConfirmationToken($tokenGenerator->generateToken());
            $this->sendPasswordResetLink($request, $user);
        } catch (\Exception $e) {
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $this->logger->error($e->getMessage());
        }

        /*
         * This operation should not call WriteListener
         * Make sure you configured: `write: false`
         */
        $this->managerRegistry->getManager()->flush();

        return new VoidOutput();
    }

    private function getUser(string $identifier): ?User
    {
        try {
            $user = $this->userProvider->loadUserByIdentifier($identifier);

            if (
                $user instanceof User
                && $user->isEnabled()
                && $user->isAccountNonExpired()
                && $user->isAccountNonLocked()
            ) {
                return $user;
            }
        } catch (AuthenticationException) {
        }

        return null;
    }

    private function sendPasswordResetLink(Request $request, User $user): void
    {
        /*
         * Support routes name as well as hard-coded URLs
         */
        try {
            $resetLink = $this->urlGenerator->generate(
                $this->passwordResetUrl,
                [
                    'token' => $user->getConfirmationToken(),
                    '_locale' => $request->getLocale(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException) {
            $resetLink = $this->passwordResetUrl.'?'.http_build_query(
                [
                    'token' => $user->getConfirmationToken(),
                    '_locale' => $request->getLocale(),
                ]
            );
        }

        $notification = new UserPasswordRequestNotification(
            $user,
            $resetLink,
            $this->translator->trans(
                'reset.password.request',
                locale: $user->getLocale()
            )
        );

        $this->notifier->send($notification, new Recipient($user->getEmail()));
    }
}
