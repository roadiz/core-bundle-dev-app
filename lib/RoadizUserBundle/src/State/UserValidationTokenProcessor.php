<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserValidationTokenInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use RZ\Roadiz\UserBundle\Event\UserEmailValidated;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UserValidationTokenProcessor implements ProcessorInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private Roles $rolesBag,
        private Security $security,
        private EventDispatcherInterface $eventDispatcher,
        private string $emailValidatedRoleName
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof UserValidationTokenInput) {
            throw new \RuntimeException(sprintf('Cannot process %s', get_class($data)));
        }

        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('User must logged in to validate its account');
        }

        $userValidationToken = $this->managerRegistry
            ->getRepository(UserValidationToken::class)
            ->findOneByValidToken($data->token);

        if (null === $userValidationToken) {
            throw new UnprocessableEntityHttpException('Token does not exist or is not valid anymore.');
        }
        if (null === $userValidationToken->getUser()) {
            throw new UnprocessableEntityHttpException('Token is not linked to any user.');
        }

        $user = $userValidationToken->getUser();

        if ($this->security->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw new AccessDeniedHttpException('Token does not belong to current account');
        }

        if (!($user instanceof User)) {
            throw new UnprocessableEntityHttpException('User is not a valid user.');
        }

        if (
            !$user->isEnabled()
            || !$user->isAccountNonExpired()
            || !$user->isAccountNonLocked()
        ) {
            throw new UnprocessableEntityHttpException('User is disabled, locked or expired.');
        }

        $user->addRoleEntity($this->rolesBag->get($this->emailValidatedRoleName));
        $this->managerRegistry->getManager()->remove($userValidationToken);
        $this->managerRegistry->getManager()->flush();

        $this->eventDispatcher->dispatch(new UserEmailValidated($user));

        return new VoidOutput();
    }
}
