<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
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

final class UserLifeCycleSubscriber implements EventSubscriber
{
    private UserViewer $userViewer;
    private EventDispatcherInterface $dispatcher;
    private PasswordHasherFactoryInterface $passwordHasherFactory;
    private LoggerInterface $logger;

    /**
     * @param UserViewer $userViewer
     * @param EventDispatcherInterface $dispatcher
     * @param PasswordHasherFactoryInterface $passwordHasherFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserViewer $userViewer,
        EventDispatcherInterface $dispatcher,
        PasswordHasherFactoryInterface $passwordHasherFactory,
        LoggerInterface $logger
    ) {
        $this->userViewer = $userViewer;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * @param PreUpdateEventArgs $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $user = $event->getEntity();
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
                        $user->setPictureUrl($user->getGravatarUrl());
                    }
                } else {
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
     *
     * @throws \Exception
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

                $this->userViewer->setUser($user);
                $this->userViewer->sendPasswordResetLink(
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
            if (empty($user->getPictureUrl())) {
                $user->setPictureUrl($user->getGravatarUrl());
            }
        }
    }
}
