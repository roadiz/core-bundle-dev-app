<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\User\UserProvider;
use RZ\Roadiz\UserBundle\Api\Dto\UserValidationRequestInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Security;

final class UserValidationRequestProcessor implements ProcessorInterface
{
    public function __construct(
        private UserProvider $userProvider,
        private Security $security,
        private UserValidationTokenManagerInterface $userValidationTokenManager,
        private ManagerRegistry $managerRegistry,
        private string $emailValidatedRoleName
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof UserValidationRequestInput) {
            throw new \RuntimeException(sprintf('Cannot process %s', get_class($data)));
        }

        $user = $this->userProvider->loadUserByIdentifier($data->identifier);

        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('User must be logged in');
        }

        if ($this->security->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw new AccessDeniedHttpException('Only current user can request email validation');
        }

        if ($this->security->isGranted($this->emailValidatedRoleName)) {
            throw new UnprocessableEntityHttpException('User email is already validated');
        }

        if ($user instanceof User) {
            $this->userValidationTokenManager->createForUser($user);
        }
        // Validation request must not call WriteListener to let ValidationRequestController persist changes.
        $this->managerRegistry->getManager()->flush();

        return new VoidOutput();
    }
}
