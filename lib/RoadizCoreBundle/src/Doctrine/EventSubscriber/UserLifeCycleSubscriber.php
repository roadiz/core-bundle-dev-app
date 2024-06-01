<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\User\UserCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserDisabledEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserEnabledEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\Documents\MediaFinders\FacebookPictureFinder;
use RZ\Roadiz\Random\TokenGenerator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
final class UserLifeCycleSubscriber
{
    public function __construct(
        private readonly UserViewer $userViewer,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly LoggerInterface $logger,
        private readonly bool $useGravatar
    ) {
    }

    /**
     * @param PreUpdateEventArgs $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User) {
            if (
                $event->hasChangedField('enabled') &&
                true === $event->getNewValue('enabled')
            ) {
                $userEvent = new UserEnabledEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }

            if (
                $event->hasChangedField('enabled') &&
                false === $event->getNewValue('enabled')
            ) {
                $userEvent = new UserDisabledEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }

            if ($event->hasChangedField('facebookName')) {
                if ('' != $event->getNewValue('facebookName')) {
                    try {
                        $facebook = new FacebookPictureFinder($user->getFacebookName());
                        $url = $facebook->getPictureUrl();
                        $user->setPictureUrl($url);
                    } catch (\Exception $e) {
                        $user->setFacebookName('');
                        if ($this->useGravatar) {
                            $user->setPictureUrl($user->getGravatarUrl());
                        }
                    }
                } elseif ($this->useGravatar) {
                    $user->setPictureUrl($user->getGravatarUrl());
                }
            }
            /*
             * Encode user password
             */
            if (
                $event->hasChangedField('password') &&
                null !== $user->getPlainPassword() &&
                '' !== $user->getPlainPassword()
            ) {
                $this->setPassword($user, $user->getPlainPassword());
                $userEvent = new UserPasswordChangedEvent($user);
                $this->dispatcher->dispatch($userEvent);
            }
        }
    }

    /**
     * @param User $user
     * @param string|null $plainPassword
     */
    protected function setPassword(User $user, ?string $plainPassword): void
    {
        if (null !== $plainPassword) {
            $hasher = $this->passwordHasherFactory->getPasswordHasher($user);
            $encodedPassword = $hasher->hash($plainPassword);
            $user->setPassword($encodedPassword);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User) {
            $userEvent = new UserUpdatedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User) {
            $userEvent = new UserDeletedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Exception
     */
    public function postPersist(LifecycleEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User) {
            $userEvent = new UserCreatedEvent($user);
            $this->dispatcher->dispatch($userEvent);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     * @throws \Throwable
     */
    public function prePersist(LifecycleEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User) {
            if (
                $user->willSendCreationConfirmationEmail() &&
                (null === $user->getPlainPassword() ||
                $user->getPlainPassword() === '')
            ) {
                /*
                 * Do not generate password for new users
                 * just send them a password reset link.
                 */
                $tokenGenerator = new TokenGenerator($this->logger);
                $user->setCredentialsExpiresAt(new \DateTime('-1 day'));
                $user->setPasswordRequestedAt(new \DateTime());
                $user->setConfirmationToken($tokenGenerator->generateToken());

                $this->userViewer->sendPasswordResetLink(
                    $user,
                    'loginResetPage',
                    '@RoadizCore/email/users/welcome_user_email.html.twig',
                    '@RoadizCore/email/users/welcome_user_email.txt.twig'
                );
            } else {
                $this->setPassword($user, $user->getPlainPassword());
            }

            /*
             * Force a Gravatar image if not defined
             */
            if (empty($user->getPictureUrl()) && $this->useGravatar) {
                $user->setPictureUrl($user->getGravatarUrl());
            }
        }
    }
}
