<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

final readonly class CustomFormAnswerNotifyMessage implements AsyncMessage
{
    public function __construct(
        private int $customFormAnswerId,
        private string $title,
        private string $locale,
    ) {
    }

    public function getCustomFormAnswerId(): int
    {
        return $this->customFormAnswerId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
