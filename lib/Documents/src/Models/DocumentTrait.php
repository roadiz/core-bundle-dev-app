<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use RZ\Roadiz\Documents\DocumentFolderGenerator;
use Symfony\Component\Serializer\Annotation as Serializer;

trait DocumentTrait
{
    #[
        Serializer\Groups(['document_mount']),
        Serializer\SerializedName('mountPath'),
    ]
    public function getMountPath(): ?string
    {
        if (null === $relativePath = $this->getRelativePath()) {
            return null;
        }
        if ($this->isPrivate()) {
            return 'private://'.$relativePath;
        } else {
            return 'public://'.$relativePath;
        }
    }

    #[
        Serializer\Ignore
    ]
    public function getMountFolderPath(): ?string
    {
        $folder = $this->getFolder();
        if (empty($folder)) {
            return null;
        }
        if ($this->isPrivate()) {
            return 'private://'.$folder;
        } else {
            return 'public://'.$folder;
        }
    }

    /**
     * Tells if current document has embed media information.
     */
    #[Serializer\Ignore()]
    public function isEmbed(): bool
    {
        return !empty($this->getEmbedId()) && !empty($this->getEmbedPlatform());
    }

    protected function initDocumentTrait(): void
    {
        $this->setFolder(DocumentFolderGenerator::generateFolderName());
    }
}
