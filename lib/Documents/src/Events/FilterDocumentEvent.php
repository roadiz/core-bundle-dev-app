<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FilterDocumentEvent extends Event
{
    public function __construct(protected DocumentInterface $document)
    {
    }

    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }
}
