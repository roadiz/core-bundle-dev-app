<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class JwtAuthenticationSuccessEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @inheritDoc
     */
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

        if (!($user instanceof User)) {
            return;
        }

        $user->setLastLogin(new \DateTime());
        $this->managerRegistry->getManager()->flush();
    }
}
