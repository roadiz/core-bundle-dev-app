<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Security;

final class ValidationRequestController
{
    private Security $security;
    private UserValidationTokenManagerInterface $userValidationTokenManager;
    private ManagerRegistry $managerRegistry;
    private string $emailValidatedRoleName;

    public function __construct(
        Security $security,
        UserValidationTokenManagerInterface $userValidationTokenManager,
        ManagerRegistry $managerRegistry,
        string $emailValidatedRoleName
    ) {
        $this->userValidationTokenManager = $userValidationTokenManager;
        $this->security = $security;
        $this->emailValidatedRoleName = $emailValidatedRoleName;
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request, User $data): User
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('User must be logged in');
        }

        if ($this->security->getUser()->getUserIdentifier() !== $data->getUserIdentifier()) {
            throw new AccessDeniedHttpException('Only current user can request email validation');
        }

        if ($this->security->isGranted($this->emailValidatedRoleName)) {
            throw new UnprocessableEntityHttpException('User email is already validated');
        }

        $this->userValidationTokenManager->createForUser($data);
        // Validation request must not call WriteListener to let ValidationRequestController persist changes.
        $this->managerRegistry->getManager()->flush();

        return $data;
    }
}
