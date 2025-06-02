<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Theme;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\Stopwatch\Stopwatch;

class StaticThemeResolver implements ThemeResolverInterface
{
    /**
     * @param array<Theme> $themes
     */
    public function __construct(
        protected array $themes,
        protected readonly Stopwatch $stopwatch,
        protected readonly bool $installMode = false,
    ) {
        usort($this->themes, [static::class, 'compareThemePriority']);
    }

    #[\Override]
    public function getBackendTheme(): Theme
    {
        $theme = new Theme();
        $theme->setAvailable(true);
        $theme->setClassName($this->getBackendClassName());
        $theme->setBackendTheme(true);

        return $theme;
    }

    /**
     * @return class-string
     */
    #[\Override]
    public function getBackendClassName(): string
    {
        /** @var class-string $className */ // php-stan hint
        $className = \Themes\Rozier\RozierApp::class;

        return $className;
    }

    #[\Override]
    public function findTheme(?string $host = null): ?Theme
    {
        $default = null;
        /*
         * Search theme by beginning at the start of the array.
         * Getting high priority theme at last
         */
        $searchThemes = $this->getFrontendThemes();

        foreach ($searchThemes as $theme) {
            if ($theme->getHostname() === $host) {
                return $theme;
            } elseif ('*' === $theme->getHostname()) {
                // Getting high priority theme at last option
                $default = $theme;
            }
        }

        return $default;
    }

    #[\Override]
    public function findThemeByClass(string $classname): ?Theme
    {
        foreach ($this->getFrontendThemes() as $theme) {
            if (ltrim($theme->getClassName(), '\\') === ltrim($classname, '\\')) {
                return $theme;
            }
        }

        return null;
    }

    #[\Override]
    public function findAll(): array
    {
        $backendThemes = [];
        if (class_exists($this->getBackendClassName())) {
            $backendThemes = [
                $this->getBackendTheme(),
            ];
        }

        return array_merge($backendThemes, $this->getFrontendThemes());
    }

    #[\Override]
    public function findById($id): ?Theme
    {
        return $this->getFrontendThemes()[$id] ?? null;
    }

    #[\Override]
    public function getFrontendThemes(): array
    {
        return $this->themes;
    }

    public static function compareThemePriority(Theme $themeA, Theme $themeB): int
    {
        /** @var class-string<AppController> $classA */
        $classA = $themeA->getClassName();
        /** @var class-string<AppController> $classB */
        $classB = $themeB->getClassName();

        if (call_user_func([$classA, 'getPriority']) === call_user_func([$classB, 'getPriority'])) {
            return 0;
        }
        if (call_user_func([$classA, 'getPriority']) > call_user_func([$classB, 'getPriority'])) {
            return 1;
        } else {
            return -1;
        }
    }
}
