<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FilterDocumentEvent extends Event
{
    protected DocumentInterface $document;

    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
    }

    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }
}
