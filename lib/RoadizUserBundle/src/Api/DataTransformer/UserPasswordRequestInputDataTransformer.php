<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\User\UserProvider;
use RZ\Roadiz\UserBundle\Api\Dto\UserPasswordRequestInput;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserPasswordRequestInputDataTransformer implements DataTransformerInterface
{
    private UserProvider $userProvider;

    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): ?User
    {
        if (!$object instanceof UserPasswordRequestInput) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($object->identifier);

            if (
                $user instanceof User
                && $user->isEnabled()
                && $user->isAccountNonExpired()
                && $user->isAccountNonLocked()
            ) {
                return $user;
            }
        } catch (AuthenticationException $exception) {
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof UserInterface) {
            return false;
        }

        return User::class === $to && UserPasswordRequestInput::class === ($context['input']['class'] ?? null);
    }
}
