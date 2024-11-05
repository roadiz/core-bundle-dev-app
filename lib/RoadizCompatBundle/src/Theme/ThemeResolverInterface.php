<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Theme;

use RZ\Roadiz\CoreBundle\Entity\Theme;

interface ThemeResolverInterface
{
    public function getBackendTheme(): Theme;

    /**
     * @return class-string
     */
    public function getBackendClassName(): string;

    public function findTheme(?string $host = null): ?Theme;

    public function findThemeByClass(string $classname): ?Theme;

    /**
     * @return Theme[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     */
    public function findById($id): ?Theme;

    /**
     * @return Theme[]
     */
    public function getFrontendThemes(): array;
}
