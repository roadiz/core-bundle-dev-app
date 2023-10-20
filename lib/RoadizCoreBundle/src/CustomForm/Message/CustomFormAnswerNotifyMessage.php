<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

final class CustomFormAnswerNotifyMessage implements AsyncMessage
{
    private int $customFormAnswerId;
    private string $title;
    private string $senderAddress;
    private string $locale;

    /**
     * @param int $customFormAnswerId
     * @param string $title
     * @param string $senderAddress
     * @param string $locale
     */
    public function __construct(int $customFormAnswerId, string $title, string $senderAddress, string $locale)
    {
        $this->customFormAnswerId = $customFormAnswerId;
        $this->title = $title;
        $this->senderAddress = $senderAddress;
        $this->locale = $locale;
    }

    public function getCustomFormAnswerId(): int
    {
        return $this->customFormAnswerId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSenderAddress(): string
    {
        return $this->senderAddress;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
