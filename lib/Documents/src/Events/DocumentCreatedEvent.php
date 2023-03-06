<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

/**
 * Event dispatched on document creation AFTER DB flushed
 */
final class DocumentCreatedEvent extends FilterDocumentEvent
{
}
