<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

use Throwable;

class EmbedDocumentAlreadyExistsException extends \InvalidArgumentException
{
    public function __construct(
        string $message = "embed.document.already_exists",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
