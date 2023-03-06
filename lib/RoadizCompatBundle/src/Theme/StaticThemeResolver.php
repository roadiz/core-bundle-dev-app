<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Theme;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\Stopwatch\Stopwatch;

class StaticThemeResolver implements ThemeResolverInterface
{
    /**
     * @var array<Theme>
     */
    protected array $themes;
    protected Stopwatch $stopwatch;
    protected bool $installMode = false;

    /**
     * @param array<Theme> $themes
     * @param Stopwatch $stopwatch
     * @param bool      $installMode
     */
    public function __construct(array $themes, Stopwatch $stopwatch, bool $installMode = false)
    {
        $this->stopwatch = $stopwatch;
        $this->installMode = $installMode;
        $this->themes = $themes;
        usort($this->themes, [static::class, 'compareThemePriority']);
    }

    /**
     * @inheritDoc
     */
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
    public function getBackendClassName(): string
    {
        /** @var class-string $className */ # php-stan hint
        $className = '\\Themes\\Rozier\\RozierApp';
        return $className;
    }

    /**
     * @inheritDoc
     */
    public function findTheme(string $host = null): ?Theme
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
            } elseif ($theme->getHostname() === '*') {
                // Getting high priority theme at last option
                $default = $theme;
            }
        }
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function findThemeByClass(string $classname): ?Theme
    {
        foreach ($this->getFrontendThemes() as $theme) {
            if (ltrim($theme->getClassName(), '\\') === ltrim($classname, '\\')) {
                return $theme;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function findById($id): ?Theme
    {
        if (isset($this->getFrontendThemes()[$id])) {
            return $this->getFrontendThemes()[$id];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getFrontendThemes(): array
    {
        return $this->themes;
    }

    /**
     * @param Theme $themeA
     * @param Theme $themeB
     *
     * @return int
     */
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
