<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractHuman;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserOutput;
use RZ\Roadiz\UserBundle\Manager\UserMetadataManagerInterface;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserTokenProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private UserValidationTokenManagerInterface $userValidationTokenManager,
        private UserMetadataManagerInterface $userMetadataManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserOutput
    {
        $user = $this->security->getUser();
        if (!$user instanceof UserInterface) {
            throw new NotFoundHttpException();
        }

        $userOutput = new UserOutput();
        $userOutput->identifier = $user->getUserIdentifier();
        $userOutput->roles = array_values($user->getRoles());

        if ($user instanceof AbstractHuman) {
            $userOutput->publicName = $user->getPublicName();
            $userOutput->firstName = $user->getFirstName();
            $userOutput->lastName = $user->getLastName();
            $userOutput->company = $user->getCompany();
        }
        if ($user instanceof User) {
            $userOutput->locale = $user->getLocale();
            $userOutput->pictureUrl = $user->getPictureUrl();

            if (null !== $userMetadata = $this->userMetadataManager->getMetadataForUser($user)) {
                $userOutput->metadata = $userMetadata->getMetadata();
            }
        }

        $userOutput->emailValidated = $this->userValidationTokenManager->isUserEmailValidated($user);

        return $userOutput;
    }
}
