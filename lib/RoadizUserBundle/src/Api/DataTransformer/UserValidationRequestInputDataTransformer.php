<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\User\UserProvider;
use RZ\Roadiz\UserBundle\Api\Dto\UserValidationRequestInput;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserValidationRequestInputDataTransformer implements DataTransformerInterface
{
    private UserProvider $userProvider;

    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): UserInterface
    {
        if (!$object instanceof UserValidationRequestInput) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        return $this->userProvider->loadUserByIdentifier($object->identifier);
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof UserInterface) {
            return false;
        }

        return User::class === $to && UserValidationRequestInput::class === ($context['input']['class'] ?? null);
    }
}
