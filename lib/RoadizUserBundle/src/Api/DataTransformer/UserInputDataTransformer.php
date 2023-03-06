<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserInput;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserInputDataTransformer implements DataTransformerInterface
{
    private UserMetadataManagerInterface $userMetadataManager;
    private Roles $rolesBag;
    private string $publicUserRoleName;

    public function __construct(UserMetadataManagerInterface $userMetadataManager, Roles $rolesBag, string $publicUserRoleName)
    {
        $this->rolesBag = $rolesBag;
        $this->publicUserRoleName = $publicUserRoleName;
        $this->userMetadataManager = $userMetadataManager;
    }

    public function transform($object, string $to, array $context = []): User
    {
        if (!$object instanceof UserInput) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        $user = new User();
        $user->setEmail($object->email);
        $user->setUsername($object->email);
        $user->setFirstName($object->firstName);
        $user->setPublicName($object->publicName);
        $user->setLastName($object->lastName);
        $user->setPhone($object->phone);
        $user->setCompany($object->company);
        $user->setJob($object->job);
        $user->setBirthday($object->birthday);
        $user->setPlainPassword($object->plainPassword);
        $user->addRoleEntity($this->rolesBag->get($this->publicUserRoleName));
        $user->sendCreationConfirmationEmail(true);

        if (null !== $object->metadata) {
            $userMetadata = $this->userMetadataManager->createMetadataForUser($user);
            $userMetadata->setMetadata($object->metadata);
        }

        return $user;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof UserInterface) {
            return false;
        }

        return User::class === $to && UserInput::class === ($context['input']['class'] ?? null);
    }
}
