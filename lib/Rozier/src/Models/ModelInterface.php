<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

/**
 * @deprecated use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface instead
 */
interface ModelInterface
{
    /**
     * Return a structured array of data.
     */
    public function toArray(): array;
}
