<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use RZ\Roadiz\UserBundle\Notifier\ValidateUserNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserValidationTokenManager implements UserValidationTokenManagerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private NotifierInterface $notifier,
        private RoleHierarchyInterface $roleHierarchy,
        private string $emailValidatedRoleName,
        private int $userValidationExpiresIn,
        private string $userValidationUrl,
    ) {
    }

    #[\Override]
    public function createForUser(UserInterface $user, bool $sendEmail = true): UserValidationToken
    {
        $existingValidationToken = $this->managerRegistry
            ->getRepository(UserValidationToken::class)
            ->findOneByUser($user);
        $tokenGenerator = new TokenGenerator($this->logger);

        if (null === $existingValidationToken) {
            $existingValidationToken = new UserValidationToken();
            $existingValidationToken->setUser($user);
            $this->managerRegistry->getManager()->persist($existingValidationToken);
        }

        $existingValidationToken->setToken($tokenGenerator->generateToken());
        $existingValidationToken->setTokenValidUntil(
            (new \DateTime())->add(new \DateInterval(sprintf('PT%dS', $this->userValidationExpiresIn)))
        );
        if ($sendEmail) {
            $this->sendUserValidationEmail($existingValidationToken);
        }

        return $existingValidationToken;
    }

    #[\Override]
    public function isUserEmailValidated(UserInterface $user): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        return \in_array($this->emailValidatedRoleName, $reachableRoles)
            || \in_array('ROLE_SUPER_ADMIN', $reachableRoles)
            || \in_array('ROLE_SUPERADMIN', $reachableRoles);
    }

    private function sendUserValidationEmail(UserValidationToken $userValidationToken): void
    {
        $user = $userValidationToken->getUser();

        if (!($user instanceof User)) {
            return;
        }

        /*
         * Support routes name as well as hard-coded URLs
         */
        try {
            $validationLink = $this->urlGenerator->generate(
                $this->userValidationUrl,
                [
                    'token' => $userValidationToken->getToken(),
                    '_locale' => $user->getLocale(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException) {
            $validationLink = $this->userValidationUrl.'?'.http_build_query(
                [
                    'token' => $userValidationToken->getToken(),
                    '_locale' => $user->getLocale(),
                ]
            );
        }

        $notification = new ValidateUserNotification(
            $user,
            $validationLink,
            $this->translator->trans(
                'validate_email.subject',
                locale: $user->getLocale(),
            )
        );

        $this->notifier->send($notification, new Recipient($user->getEmail()));
    }
}
