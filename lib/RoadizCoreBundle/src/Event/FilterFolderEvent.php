<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterFolderEvent extends Event
{
    public function __construct(protected readonly Folder $folder)
    {
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }
}
