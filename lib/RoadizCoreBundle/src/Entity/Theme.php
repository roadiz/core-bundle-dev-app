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

    /**
     * @return boolean
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param boolean $available
     *
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
     *
     * @return boolean
     */
    public function isStaticTheme(): bool
    {
        return (bool) $this->staticTheme;
    }

    /**
     * @param boolean $staticTheme
     * @return $this
     */
    public function setStaticTheme(bool $staticTheme): Theme
    {
        $this->staticTheme = (bool) $staticTheme;
        return $this;
    }

    /**
     * Alias for getInformations.
     *
     * @return array
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
     *
     * @return array
     */
    public function getInformations(): array
    {
        $class = $this->getClassName();

        if (class_exists($class)) {
            $reflector = new \ReflectionClass($class);
            if ($reflector->isSubclassOf('\\RZ\\Roadiz\\CMS\\Controllers\\AppController')) {
                return [
                    'name' => call_user_func([$class, 'getThemeName']),
                    'author' => call_user_func([$class, 'getThemeAuthor']),
                    'copyright' => call_user_func([$class, 'getThemeCopyright']),
                    'dir' => call_user_func([$class, 'getThemeDir'])
                ];
            }
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
     * @return $this
     */
    public function setClassName(string $className): Theme
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     *
     * @return $this
     */
    public function setHostname(string $hostname): Theme
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoutePrefix(): string
    {
        return $this->routePrefix;
    }

    /**
     * @param string $routePrefix
     *
     * @return $this
     */
    public function setRoutePrefix(string $routePrefix): Theme
    {
        $this->routePrefix = $routePrefix;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isBackendTheme(): bool
    {
        return $this->backendTheme;
    }

    /**
     * @param boolean $backendTheme
     * @return $this
     */
    public function setBackendTheme(bool $backendTheme): Theme
    {
        $this->backendTheme = $backendTheme;
        return $this;
    }
}
