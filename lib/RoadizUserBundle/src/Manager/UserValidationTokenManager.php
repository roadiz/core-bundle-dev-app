<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use RZ\Roadiz\Random\TokenGenerator;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserValidationTokenManager implements UserValidationTokenManagerInterface
{
    private ManagerRegistry $managerRegistry;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;
    private EmailManager $emailManager;
    private Settings $settingsBag;
    private RoleHierarchyInterface $roleHierarchy;
    private string $emailValidatedRoleName;
    private int $userValidationExpiresIn;
    private string $userValidationUrl;

    public function __construct(
        ManagerRegistry $managerRegistry,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        EmailManager $emailManager,
        Settings $settingsBag,
        RoleHierarchyInterface $roleHierarchy,
        string $emailValidatedRoleName,
        int $userValidationExpiresIn,
        string $userValidationUrl
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->userValidationExpiresIn = $userValidationExpiresIn;
        $this->emailManager = $emailManager;
        $this->userValidationUrl = $userValidationUrl;
        $this->settingsBag = $settingsBag;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->roleHierarchy = $roleHierarchy;
        $this->emailValidatedRoleName = $emailValidatedRoleName;
    }

    public function createForUser(User $user): UserValidationToken
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
        $this->sendUserValidationEmail($existingValidationToken);
        return $existingValidationToken;
    }

    public function isUserEmailValidated(UserInterface $user): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());
        return \in_array($this->emailValidatedRoleName, $reachableRoles) ||
            \in_array('ROLE_SUPER_ADMIN', $reachableRoles) ||
            \in_array('ROLE_SUPERADMIN', $reachableRoles);
    }


    private function sendUserValidationEmail(UserValidationToken $userValidationToken): void
    {
        $emailContact = $this->settingsBag->get('support_email_address', null) ??
            $this->settingsBag->get('email_sender', null);
        $siteName = $this->settingsBag->get('site_name');

        /*
         * Support routes name as well as hard-coded URLs
         */
        try {
            $validationLink = $this->urlGenerator->generate(
                $this->userValidationUrl,
                [
                    'token' => $userValidationToken->getToken(),
                    '_locale' => $userValidationToken->getUser()?->getLocale(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException $exception) {
            $validationLink = $this->userValidationUrl . '?' . http_build_query(
                [
                    'token' => $userValidationToken->getToken(),
                    '_locale' => $userValidationToken->getUser()?->getLocale(),
                ]
            );
        }

        $this->emailManager->setAssignation(
            [
                'validationLink' => $validationLink,
                'user' => $userValidationToken->getUser(),
                'site' => $siteName,
                'mailContact' => $emailContact,
            ]
        );
        $this->emailManager->setEmailTemplate('@RoadizUser/email/users/validate_email.html.twig');
        $this->emailManager->setEmailPlainTextTemplate('@RoadizUser/email/users/validate_email.txt.twig');
        $this->emailManager->setSubject(
            $this->translator->trans(
                'validate_email.subject'
            )
        );
        $this->emailManager->setReceiver($userValidationToken->getUser()->getEmail());
        $this->emailManager->setSender(new Address($emailContact, $siteName ?? ''));
        $this->emailManager->send();
    }
}
