<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterCacheEvent extends Event
{
    private Collection $messageCollection;
    private Collection $errorCollection;

    public function __construct()
    {
        $this->messageCollection = new ArrayCollection();
        $this->errorCollection = new ArrayCollection();
    }

    public function addMessage(string $message, ?string $classname = null, ?string $description = null): void
    {
        $this->messageCollection->add([
            'clearer' => $classname,
            'description' => $description,
            'message' => $message,
        ]);
    }

    public function addError(string $message, ?string $classname = null, ?string $description = null): void
    {
        $this->errorCollection->add([
            'clearer' => $classname,
            'description' => $description,
            'message' => $message,
        ]);
    }

    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getMessages(): array
    {
        return $this->messageCollection->toArray();
    }
}
