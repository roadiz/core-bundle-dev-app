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
use RZ\Roadiz\UserBundle\Notifier\PasswordResetLinkNotifier;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
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
        private RateLimiterFactoryInterface $passwordRequestEmailLimiter,
        private ManagerRegistry $managerRegistry,
        private RequestStack $requestStack,
        private UserProvider $userProvider,
        private PasswordResetLinkNotifier $passwordResetLinkNotifier,
        private TranslatorInterface $translator,
        private CaptchaServiceInterface $recaptchaService,
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

        // Per-IP limiting alone lets an IP-rotating attacker flood a single
        // victim's mailbox: also cap requests per targeted identifier.
        $emailLimiter = $this->passwordRequestEmailLimiter->create(mb_strtolower(trim($data->identifier)));
        $emailLimit = $emailLimiter->consume();
        if (false === $emailLimit->isAccepted()) {
            throw new TooManyRequestsHttpException($emailLimit->getRetryAfter()->getTimestamp());
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
            $this->passwordResetLinkNotifier->notify(
                $user,
                $request->getLocale(),
                $this->translator->trans('reset.password.request', locale: $user->getLocale()),
                '@RoadizUser/email/users/reset_password_email.html.twig',
                '@RoadizUser/email/users/reset_password_email.txt.twig',
            );
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
}
