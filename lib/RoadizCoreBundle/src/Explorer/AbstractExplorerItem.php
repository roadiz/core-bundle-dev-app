<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

abstract class AbstractExplorerItem implements ExplorerItemInterface
{
    protected function getEditItemPath(): ?string
    {
        return null;
    }

    protected function getThumbnail(): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'classname' => $this->getAlternativeDisplayable() ?? '',
            'displayable' => $this->getDisplayable(),
            'editItem' => $this->getEditItemPath(),
            'thumbnail' => $this->getThumbnail()
        ];
    }
}
