<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

final class DocumentTypeNotAllowedException extends \RuntimeException
{
    public function __construct(
        private readonly string $filename,
        private readonly string $extension,
    ) {
        parent::__construct(sprintf('File "%s" has a forbidden extension ".%s".', $filename, $extension));
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }
}
