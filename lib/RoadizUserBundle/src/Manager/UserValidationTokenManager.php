<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Mailer\EmailManagerFactory;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use Symfony\Component\Mime\Address;
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
        private EmailManagerFactory $emailManagerFactory,
        private Settings $settingsBag,
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
        $emailManager = $this->emailManagerFactory->create();
        $emailContact = $this->settingsBag->get('support_email_address', null) ??
            $this->settingsBag->get('email_sender', null);
        $siteName = $this->settingsBag->get('site_name');

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

        $emailManager->setAssignation(
            [
                'validationLink' => $validationLink,
                'user' => $user,
                'site' => $siteName,
                'mailContact' => $emailContact,
            ]
        );
        $emailManager->setEmailTemplate('@RoadizUser/email/users/validate_email.html.twig');
        $emailManager->setEmailPlainTextTemplate('@RoadizUser/email/users/validate_email.txt.twig');
        $emailManager->setSubject(
            $this->translator->trans(
                'validate_email.subject'
            )
        );
        $emailManager->setReceiver($user->getEmail());
        $emailManager->setSender(new Address($emailContact, $siteName ?? ''));
        $emailManager->send();
    }
}
