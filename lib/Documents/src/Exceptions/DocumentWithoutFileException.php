<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentWithoutFileException extends \RuntimeException
{
    public function __construct(private readonly DocumentInterface $document)
    {
        parent::__construct(sprintf('Document (%s) does not have a file on system.', (string) $document));
    }

    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }
}
