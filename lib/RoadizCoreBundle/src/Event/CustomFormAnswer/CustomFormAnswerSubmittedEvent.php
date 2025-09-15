<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\CustomFormAnswer;

use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\EventDispatcher\Event;

final class CustomFormAnswerSubmittedEvent extends Event
{
    public function __construct(private readonly CustomFormAnswer $customFormAnswer)
    {
    }

    public function getCustomFormAnswer(): CustomFormAnswer
    {
        return $this->customFormAnswer;
    }
}
