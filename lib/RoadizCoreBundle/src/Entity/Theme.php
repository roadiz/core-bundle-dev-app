<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * Themes describe a database entity to store
 * front-end and back-end controllers.
 */
class Theme extends AbstractEntity
{
    protected string $hostname = '*';
    protected bool $staticTheme = false;
    protected bool $available = false;
    /**
     * @var class-string
     */
    protected string $className;
    private string $routePrefix = '';
    private bool $backendTheme = false;

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @return $this
     */
    public function setAvailable(bool $available): Theme
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Static means that your theme is not suitable for responding from
     * nodes urls but only static routes.
     */
    public function isStaticTheme(): bool
    {
        return (bool) $this->staticTheme;
    }

    /**
     * @return $this
     */
    public function setStaticTheme(bool $staticTheme): Theme
    {
        $this->staticTheme = (bool) $staticTheme;

        return $this;
    }

    /**
     * Alias for getInformations.
     */
    public function getInformation(): array
    {
        return $this->getInformations();
    }

    /**
     * Get theme information in an array.
     *
     * - name
     * - author
     * - copyright
     * - dir
     */
    public function getInformations(): array
    {
        $class = $this->getClassName();

        if (class_exists($class)) {
            $nameCallable = [$class, 'getThemeName'];
            $authorCallable = [$class, 'getThemeAuthor'];
            $copyrightCallable = [$class, 'getThemeCopyright'];
            $dirCallable = [$class, 'getThemeDir'];

            return [
                'name' => \is_callable($nameCallable) ? call_user_func($nameCallable) : null,
                'author' => \is_callable($authorCallable) ? call_user_func($authorCallable) : null,
                'copyright' => \is_callable($copyrightCallable) ? call_user_func($copyrightCallable) : null,
                'dir' => \is_callable($dirCallable) ? call_user_func($dirCallable) : null,
            ];
        }

        return [];
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param class-string $className
     *
     * @return $this
     */
    public function setClassName(string $className): Theme
    {
        $this->className = $className;

        return $this;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return $this
     */
    public function setHostname(string $hostname): Theme
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getRoutePrefix(): string
    {
        return $this->routePrefix;
    }

    /**
     * @return $this
     */
    public function setRoutePrefix(string $routePrefix): Theme
    {
        $this->routePrefix = $routePrefix;

        return $this;
    }

    public function isBackendTheme(): bool
    {
        return $this->backendTheme;
    }

    /**
     * @return $this
     */
    public function setBackendTheme(bool $backendTheme): Theme
    {
        $this->backendTheme = $backendTheme;

        return $this;
    }
}
