<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Message\Handler;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use RZ\Roadiz\UserBundle\Message\UserPasswordRequestNotifyMessage;
use RZ\Roadiz\UserBundle\Notifier\UserPasswordRequestNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
final readonly class UserPasswordRequestNotifyMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private NotifierInterface $notifier,
    ) {
    }

    public function __invoke(UserPasswordRequestNotifyMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());

        if (!$user instanceof User) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        $email = $user->getEmail();
        if (null === $email) {
            throw new UnrecoverableMessageHandlingException('User email is null');
        }

        $notification = new UserPasswordRequestNotification(
            $user,
            $message->getResetLink(),
            $message->getSubject()
        );

        $this->notifier->send($notification, new Recipient($email));
    }
}
