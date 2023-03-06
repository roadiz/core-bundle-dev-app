<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserTokenInput;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserTokenInputDataTransformer implements DataTransformerInterface
{
    private ManagerRegistry $managerRegistry;
    private Roles $rolesBag;
    private Security $security;
    private string $emailValidatedRoleName;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Roles $rolesBag,
        Security $security,
        string $emailValidatedRoleName
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->rolesBag = $rolesBag;
        $this->emailValidatedRoleName = $emailValidatedRoleName;
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): User
    {
        if (!$object instanceof UserTokenInput) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('User must logged in to validate its account');
        }

        $userValidationToken = $this->managerRegistry
            ->getRepository(UserValidationToken::class)
            ->findOneByValidToken($object->token);

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

        if (
            $user->isEnabled()
            && $user->isAccountNonExpired()
            && $user->isAccountNonLocked()
        ) {
            $user->addRoleEntity($this->rolesBag->get($this->emailValidatedRoleName));
            $this->managerRegistry->getManager()->remove($userValidationToken);
            return $user;
        }

        throw new UnprocessableEntityHttpException('User is disabled, locked or expired.');
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof UserInterface) {
            return false;
        }

        return User::class === $to && UserTokenInput::class === ($context['input']['class'] ?? null);
    }
}
