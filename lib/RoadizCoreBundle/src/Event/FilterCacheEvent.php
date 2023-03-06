<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterCacheEvent extends Event
{
    private Collection $messageCollection;
    private Collection $errorCollection;

    public function __construct()
    {
        $this->messageCollection = new ArrayCollection();
        $this->errorCollection = new ArrayCollection();
    }

    /**
     * @param string $message
     * @param string|null $classname
     * @param string|null $description
     */
    public function addMessage(string $message, ?string $classname = null, ?string $description = null): void
    {
        $this->messageCollection->add([
            "clearer" => $classname,
            "description" => $description,
            "message" => $message,
        ]);
    }

    /**
     * @param string $message
     * @param string|null $classname
     * @param string|null $description
     */
    public function addError(string $message, ?string $classname = null, ?string $description = null): void
    {
        $this->errorCollection->add([
            "clearer" => $classname,
            "description" => $description,
            "message" => $message,
        ]);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messageCollection->toArray();
    }
}
