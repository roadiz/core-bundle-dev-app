<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\UserBundle\Api\Dto\UserInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use RZ\Roadiz\UserBundle\Notifier\PasswordResetLinkNotifier;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserSignupProcessor implements ProcessorInterface
{
    use CaptchaProtectedTrait;
    use SignupProcessorTrait;

    public function __construct(
        private ValidatorInterface $validator,
        private Security $security,
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
        private RateLimiterFactoryInterface $userSignupLimiter,
        private CaptchaServiceInterface $recaptchaService,
        private ProcessorInterface $persistProcessor,
        private UserMetadataManagerInterface $userMetadataManager,
        private ManagerRegistry $managerRegistry,
        private PasswordResetLinkNotifier $passwordResetLinkNotifier,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private string $publicUserRoleName,
    ) {
    }

    #[\Override]
    protected function getCaptchaService(): CaptchaServiceInterface
    {
        return $this->recaptchaService;
    }

    #[\Override]
    protected function getSecurity(): Security
    {
        return $this->security;
    }

    #[\Override]
    protected function getUserSignupLimiter(): RateLimiterFactoryInterface
    {
        return $this->userSignupLimiter;
    }

    #[\Override]
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof UserInput) {
            throw new BadRequestHttpException(sprintf('Cannot process %s', $data::class));
        }

        $request = $this->requestStack->getCurrentRequest();
        $this->validateRequest($request);
        $this->validateCaptchaHeader($request);

        // Do not reveal that this email is already registered: notify the
        // existing account holder out-of-band instead of returning a 422 that
        // an anonymous caller could use to enumerate accounts.
        $existingUser = $this->managerRegistry->getRepository(User::class)->findOneBy(['email' => $data->email]);
        if ($existingUser instanceof User) {
            $this->notifySignupAttemptOnExistingAccount($existingUser, $request);

            return new VoidOutput();
        }

        $user = $this->createUser($data);
        $user->setPlainPassword($data->plainPassword);
        $user->setUserRoles([
            ...$user->getUserRoles(),
            $this->publicUserRoleName,
        ]);
        $user->sendCreationConfirmationEmail(true);
        $user->setLocale($request?->getLocale());

        $this->validator->validate($user);

        $this->eventDispatcher->dispatch(new UserSignedUp($user));
        // Process and persist user to database before returning a VoidOutput
        $user = $this->persistProcessor->process($user, $operation, $uriVariables, $context);

        if (null !== $data->metadata) {
            $userMetadata = $this->userMetadataManager->createMetadataForUser($user);
            $userMetadata->setMetadata($data->metadata);
            $this->persistProcessor->process($userMetadata, $operation, $uriVariables, $context);
        }

        return new VoidOutput();
    }

    private function notifySignupAttemptOnExistingAccount(User $user, ?Request $request): void
    {
        try {
            $tokenGenerator = new TokenGenerator($this->logger);
            $user->setPasswordRequestedAt(new \DateTime());
            $user->setConfirmationToken($tokenGenerator->generateToken());
            $this->passwordResetLinkNotifier->notify(
                $user,
                $request?->getLocale() ?? 'en',
                $this->translator->trans('signup.email_already_used.notification', locale: $user->getLocale()),
                '@RoadizUser/email/users/signup_attempt_email.html.twig',
                '@RoadizUser/email/users/signup_attempt_email.txt.twig',
            );
            $this->managerRegistry->getManager()->flush();
        } catch (\Exception $e) {
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $this->logger->error($e->getMessage());
        }
    }
}
