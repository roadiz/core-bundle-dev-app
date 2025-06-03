<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

abstract class AbstractExplorerItem implements ExplorerItemInterface
{
    protected function getEditItemPath(): ?string
    {
        return null;
    }

    protected function getThumbnail(): BaseDocumentInterface|array|null
    {
        return null;
    }

    protected function isPublished(): bool
    {
        return true;
    }

    protected function getColor(): ?string
    {
        return null;
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'classname' => $this->getAlternativeDisplayable() ?? '',
            'displayable' => $this->getDisplayable(),
            'editItem' => $this->getEditItemPath(),
            'thumbnail' => $this->getThumbnail(),
            'published' => $this->isPublished(),
            'color' => $this->getColor(),
        ];
    }
}
