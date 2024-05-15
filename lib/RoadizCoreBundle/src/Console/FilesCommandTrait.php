<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

trait FilesCommandTrait
{
    /**
     * @return string
     */
    protected function getPublicFolderName(): string
    {
        return '/exported_public';
    }

    /**
     * @return string
     */
    protected function getPrivateFolderName(): string
    {
        return '/exported_private';
    }

    /**
     * @return string
     */
    protected function getFontsFolderName(): string
    {
        return '/exported_fonts';
    }
}
