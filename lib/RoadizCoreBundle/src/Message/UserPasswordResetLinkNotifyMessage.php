<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final readonly class UserPasswordResetLinkNotifyMessage implements AsyncMessage
{
    public function __construct(
        private int $userId,
        private string $resetLink,
        private string $subject,
        private string $htmlTemplate,
        private string $textTemplate,
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

    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }

    public function getTextTemplate(): string
    {
        return $this->textTemplate;
    }
}
