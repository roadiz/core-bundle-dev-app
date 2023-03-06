<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentWithoutFileException extends \RuntimeException
{
    private DocumentInterface $document;

    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
        parent::__construct(sprintf('Document (%s) does not have a file on system.', (string) $document));
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }
}
