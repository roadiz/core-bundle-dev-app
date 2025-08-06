<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use RZ\Roadiz\Documents\DocumentFolderGenerator;
use Symfony\Component\Serializer\Attribute as Serializer;

trait DocumentTrait
{
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

    protected function initDocumentTrait(): void
    {
        $this->setFolder(DocumentFolderGenerator::generateFolderName());
    }
}
