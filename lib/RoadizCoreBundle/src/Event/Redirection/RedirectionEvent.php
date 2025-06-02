<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Redirection;

use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Contracts\EventDispatcher\Event;

abstract class RedirectionEvent extends Event
{
    public function __construct(protected ?Redirection $redirection)
    {
    }

    public function getRedirection(): ?Redirection
    {
        return $this->redirection;
    }

    public function setRedirection(?Redirection $redirection): RedirectionEvent
    {
        $this->redirection = $redirection;

        return $this;
    }
}
