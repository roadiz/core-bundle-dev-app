<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaServiceInterface;
use RZ\Roadiz\UserBundle\Api\Dto\UserInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UserSignupProcessor implements ProcessorInterface
{
    use RecaptchaProtectedTrait;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RateLimiterFactory $userSignupLimiter,
        private readonly RecaptchaServiceInterface $recaptchaService,
        private readonly ProcessorInterface $persistProcessor,
        private readonly UserMetadataManagerInterface $userMetadataManager,
        private readonly Roles $rolesBag,
        private readonly string $publicUserRoleName,
        private readonly string $recaptchaHeaderName = 'x-g-recaptcha-response',
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
        if (!$data instanceof UserInput) {
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
        $user->setPublicName($data->publicName);
        $user->setLastName($data->lastName);
        $user->setPhone($data->phone);
        $user->setCompany($data->company);
        $user->setJob($data->job);
        $user->setBirthday($data->birthday);
        $user->setPlainPassword($data->plainPassword);
        $user->addRoleEntity($this->rolesBag->get($this->publicUserRoleName));
        $user->sendCreationConfirmationEmail(true);
        $user->setLocale($request->getLocale());

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
}
