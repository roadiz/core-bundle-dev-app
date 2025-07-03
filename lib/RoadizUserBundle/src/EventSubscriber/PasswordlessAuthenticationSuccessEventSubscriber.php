<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use RZ\Roadiz\UserBundle\Event\UserEmailValidated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class PasswordlessAuthenticationSuccessEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private EventDispatcherInterface $eventDispatcher,
        private string $emailValidatedRoleName,
        private string $passwordlessUserRoleName,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        /*
         * Check if user is a passwordless user
         */
        if (!($user instanceof User) || !\in_array($this->passwordlessUserRoleName, $user->getRoles())) {
            return;
        }

        $userValidationToken = $this->managerRegistry
            ->getRepository(UserValidationToken::class)
            ->findOneByUser($user);

        if (null !== $userValidationToken) {
            $user->setUserRoles([
                ...$user->getUserRoles(),
                $this->emailValidatedRoleName,
            ]);

            $this->managerRegistry->getManager()->remove($userValidationToken);
            $this->managerRegistry->getManager()->flush();

            $this->eventDispatcher->dispatch(new UserEmailValidated($user));
            $this->managerRegistry->getManager()->flush();
        }
    }
}
