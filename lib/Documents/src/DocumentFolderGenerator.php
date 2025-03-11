<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

final readonly class DocumentFolderGenerator
{
    /**
     * Generate a random folder name for documents, 12 characters long.
     */
    public static function generateFolderName(): string
    {
        return \mb_substr(hash('crc32c', microtime()), 0, 12);
    }
}
