<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use RZ\Roadiz\UserBundle\Api\Dto\PasswordlessUserInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Event\PasswordlessUserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class PasswordlessUserSignupProcessor implements ProcessorInterface
{
    use RecaptchaProtectedTrait;

    public function __construct(
        private LoginLinkHandlerInterface $loginLinkHandler,
        private ValidatorInterface $validator,
        private Security $security,
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
        private RateLimiterFactory $userSignupLimiter,
        private RecaptchaServiceInterface $recaptchaService,
        private ProcessorInterface $persistProcessor,
        private UserMetadataManagerInterface $userMetadataManager,
        private Roles $rolesBag,
        private LoginLinkSenderInterface $loginLinkSender,
        private string $publicUserRoleName,
        private string $passwordlessUserRoleName,
        private string $recaptchaHeaderName = 'x-g-recaptcha-response',
    ) {
    }

    protected function getRecaptchaService(): RecaptchaServiceInterface
    {
        return $this->recaptchaService;
    }

    protected function getRecaptchaHeaderName(): string
    {
        return $this->recaptchaHeaderName;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof PasswordlessUserInput) {
            throw new BadRequestHttpException(sprintf('Cannot process %s', get_class($data)));
        }

        if ($this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Cannot sign-up: you\'re already authenticated.');
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $limiter = $this->userSignupLimiter->create($request->getClientIp());
            $limit = $limiter->consume();
            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
            }
        }

        $this->validateRecaptchaHeader($request);

        $user = new User();
        $user->setEmail($data->email);
        $user->setUsername($data->email);
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);
        $user->setPhone($data->phone);
        $user->setCompany($data->company);
        $user->setJob($data->job);
        $user->setBirthday($data->birthday);
        $user->addRoleEntity($this->rolesBag->get($this->publicUserRoleName));
        $user->addRoleEntity($this->rolesBag->get($this->passwordlessUserRoleName));
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

        # Send user first login link
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user, $request);
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);

        return new VoidOutput();
    }
}
