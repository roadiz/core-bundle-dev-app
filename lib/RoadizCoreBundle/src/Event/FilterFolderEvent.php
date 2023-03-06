<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterFolderEvent extends Event
{
    protected Folder $folder;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }
}
