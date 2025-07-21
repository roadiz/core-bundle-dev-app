<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\UserBundle\Api\Dto\UserInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class UserSignupProcessor implements ProcessorInterface
{
    use CaptchaProtectedTrait;
    use SignupProcessorTrait;

    public function __construct(
        private ValidatorInterface $validator,
        private Security $security,
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
        private RateLimiterFactory $userSignupLimiter,
        private CaptchaServiceInterface $recaptchaService,
        private ProcessorInterface $persistProcessor,
        private UserMetadataManagerInterface $userMetadataManager,
        private Roles $rolesBag,
        private string $publicUserRoleName,
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
        if (!$data instanceof UserInput) {
            throw new BadRequestHttpException(sprintf('Cannot process %s', get_class($data)));
        }

        $request = $this->requestStack->getCurrentRequest();
        $this->validateRequest($request);
        $this->validateCaptchaHeader($request);

        $user = $this->createUser($data);
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
