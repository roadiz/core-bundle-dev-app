<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Redirection;

use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Contracts\EventDispatcher\Event;

abstract class RedirectionEvent extends Event
{
    protected ?Redirection $redirection;

    /**
     * @param Redirection|null $redirection
     */
    public function __construct(?Redirection $redirection)
    {
        $this->redirection = $redirection;
    }

    /**
     * @return Redirection|null
     */
    public function getRedirection(): ?Redirection
    {
        return $this->redirection;
    }

    /**
     * @param Redirection|null $redirection
     * @return RedirectionEvent
     */
    public function setRedirection(?Redirection $redirection): RedirectionEvent
    {
        $this->redirection = $redirection;
        return $this;
    }
}
