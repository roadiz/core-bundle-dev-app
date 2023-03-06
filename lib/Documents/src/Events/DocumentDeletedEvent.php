<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

/**
 * Event dispatched on document deletion BEFORE DB flushed
 */
final class DocumentDeletedEvent extends FilterDocumentEvent
{
}
