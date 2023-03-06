<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractHuman;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserOutput;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserOutputDataTransformer implements DataTransformerInterface
{
    private UserMetadataManagerInterface $userMetadataManager;
    private UserValidationTokenManagerInterface $userValidationTokenManager;

    public function __construct(
        UserValidationTokenManagerInterface $userValidationTokenManager,
        UserMetadataManagerInterface $userMetadataManager
    ) {
        $this->userValidationTokenManager = $userValidationTokenManager;
        $this->userMetadataManager = $userMetadataManager;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): UserOutput
    {
        if (!$object instanceof UserInterface) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        $userOutput = new UserOutput();
        $userOutput->identifier = $object->getUserIdentifier();
        $userOutput->roles = array_values($object->getRoles());

        if ($object instanceof AbstractHuman) {
            $userOutput->publicName = $object->getPublicName();
            $userOutput->firstName = $object->getFirstName();
            $userOutput->lastName = $object->getLastName();
            $userOutput->phone = $object->getPhone();
            $userOutput->company = $object->getCompany();
            $userOutput->job = $object->getJob();
            $userOutput->birthday = $object->getBirthday();
        }
        if ($object instanceof User) {
            $userOutput->locale = $object->getLocale();
            $userOutput->pictureUrl = $object->getPictureUrl();

            if (null !== $userMetadata = $this->userMetadataManager->getMetadataForUser($object)) {
                $userOutput->metadata = $userMetadata->getMetadata();
            }
        }

        $userOutput->emailValidated = $this->userValidationTokenManager->isUserEmailValidated($object);
        return $userOutput;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === UserOutput::class && $data instanceof UserInterface;
    }
}
