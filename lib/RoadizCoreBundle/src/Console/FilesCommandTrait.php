<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

trait FilesCommandTrait
{
    protected function getPublicFolderName(): string
    {
        return '/exported_public';
    }

    protected function getPrivateFolderName(): string
    {
        return '/exported_private';
    }

    protected function getFontsFolderName(): string
    {
        return '/exported_fonts';
    }
}
