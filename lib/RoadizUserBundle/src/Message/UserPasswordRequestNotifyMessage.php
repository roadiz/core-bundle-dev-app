<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

final readonly class UserPasswordRequestNotifyMessage implements AsyncMessage
{
    public function __construct(
        private int $userId,
        private string $resetLink,
        private string $subject,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getResetLink(): string
    {
        return $this->resetLink;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }
}
