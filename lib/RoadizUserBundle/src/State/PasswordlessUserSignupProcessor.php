<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use RZ\Roadiz\UserBundle\Api\Dto\PasswordlessUserInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Event\PasswordlessUserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class PasswordlessUserSignupProcessor implements ProcessorInterface
{
    use CaptchaProtectedTrait;
    use SignupProcessorTrait;

    public function __construct(
        private LoginLinkHandlerInterface $loginLinkHandler,
        private ValidatorInterface $validator,
        private Security $security,
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
        private RateLimiterFactory $userSignupLimiter,
        private CaptchaServiceInterface $recaptchaService,
        private ProcessorInterface $persistProcessor,
        private UserMetadataManagerInterface $userMetadataManager,
        private Roles $rolesBag,
        private LoginLinkSenderInterface $loginLinkSender,
        private string $publicUserRoleName,
        private string $passwordlessUserRoleName,
    ) {
    }

    protected function getCaptchaService(): CaptchaServiceInterface
    {
        return $this->recaptchaService;
    }

    protected function getSecurity(): Security
    {
        return $this->security;
    }

    protected function getUserSignupLimiter(): RateLimiterFactory
    {
        return $this->userSignupLimiter;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof PasswordlessUserInput) {
            throw new BadRequestHttpException(sprintf('Cannot process %s', get_class($data)));
        }
        $request = $this->requestStack->getCurrentRequest();
        $this->validateRequest($request);
        $this->validateCaptchaHeader($request);

        $user = $this->createUser($data);
        $user->addRoleEntity($this->rolesBag->get($this->publicUserRoleName));
        $user->addRoleEntity($this->rolesBag->get($this->passwordlessUserRoleName));
        /*
         * We don't want to send an email right now, we will send a login link instead.
         */
        $user->sendCreationConfirmationEmail(false);
        $user->setLocale($request->getLocale());

        $this->validator->validate($user);

        $this->eventDispatcher->dispatch(new PasswordlessUserSignedUp($user));
        // Process and persist user to database before returning a VoidOutput
        $user = $this->persistProcessor->process($user, $operation, $uriVariables, $context);

        if (null !== $data->metadata) {
            $userMetadata = $this->userMetadataManager->createMetadataForUser($user);
            $userMetadata->setMetadata($data->metadata);
            $this->persistProcessor->process($userMetadata, $operation, $uriVariables, $context);
        }

        /*
         * Send user first login link, this will also set user as EMAIL_VALIDATED
         */
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user, $request);
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);

        return new VoidOutput();
    }
}
