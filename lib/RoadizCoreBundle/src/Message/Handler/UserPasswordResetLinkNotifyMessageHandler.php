<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Message\UserPasswordResetLinkNotifyMessage;
use RZ\Roadiz\CoreBundle\Notifier\ResetPasswordNotification;
use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
final readonly class UserPasswordResetLinkNotifyMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private NotifierInterface $notifier,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(UserPasswordResetLinkNotifyMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());

        if (!$user instanceof User) {
            throw new UnrecoverableMessageHandlingException('User not found');
        }

        $email = $user->getEmail();
        if (null === $email) {
            // Deterministically unrecoverable: retrying will never produce an email
            // to send to, so roll back the token flushed by the synchronous request
            // instead of leaving it live with no notification ever delivered.
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $this->managerRegistry->getManager()->flush();

            throw new UnrecoverableMessageHandlingException('User email is null');
        }

        $notification = new ResetPasswordNotification(
            $user,
            $message->getResetLink(),
            $message->getSubject(),
            [],
            $message->getHtmlTemplate(),
            $message->getTextTemplate(),
        );

        $this->notifier->send($notification, new Recipient($email));
    }
}
