<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Theme;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Exception\ThemeClassNotValidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Filesystem\Filesystem;

final class ThemeInfo
{
    private string $name;
    private string $themeName;
    /**
     * @var class-string|null
     */
    private ?string $classname = null;
    private Filesystem $filesystem;
    private ?string $themePath = null;
    private static array $protectedThemeNames = ['Rozier'];

    /**
     * @param string $name Short theme name or FQN classname
     *
     * @throws ThemeClassNotValidException
     */
    public function __construct(string $name, private readonly string $projectDir)
    {
        $this->filesystem = new Filesystem();

        if (class_exists($name)) {
            /*
             * If name is a FQN classname
             */
            $this->classname = $this->validateClassname($name);
            $this->name = $this->extractNameFromClassname($this->classname);
        } else {
            $this->name = $this->validateName($name);
        }
        $this->themeName = $this->getThemeNameFromName();
    }

    public function isProtected(): bool
    {
        return in_array($this->getThemeName(), self::$protectedThemeNames) && 'Rozier' !== $this->getThemeName();
    }

    /**
     * @return class-string
     *
     * @throws ThemeClassNotValidException
     */
    protected function guessClassnameFromThemeName(string $themeName): string
    {
        $className = match ($themeName) {
            'RozierApp', 'RozierTheme', 'Rozier' => '\\Themes\\Rozier\\RozierApp',
            default => '\\Themes\\'.$themeName.'\\'.$themeName.'App',
        };

        if (class_exists($className)) {
            return $className;
        } else {
            throw new ThemeClassNotValidException(sprintf('“%s” theme is not available in your project.', $className));
        }
    }

    /**
     * @param class-string $classname
     *
     * @throws ThemeClassNotValidException
     */
    protected function extractNameFromClassname(string $classname): string
    {
        $shortName = $this->getThemeReflectionClass($classname)->getShortName();

        return preg_replace('#(?:Theme)?(?:App)?$#', '', $shortName);
    }

    /**
     * @param class-string $classname
     *
     * @return class-string
     *
     * @throws ThemeClassNotValidException
     */
    protected function validateClassname(string $classname): string
    {
        if (null !== $reflection = $this->getThemeReflectionClass($classname)) {
            /** @var class-string<AppController> $class */
            $class = $reflection->getName();
            if (method_exists($class, 'getThemeMainClass')) {
                return $class::getThemeMainClass();
            }
        }
        throw new \RuntimeException('Theme class '.$classname.' does not exist.');
    }

    protected function validateName(string $name): string
    {
        if (1 !== preg_match('#^[A-Z][a-zA-Z]+$#', $name)) {
            throw new LogicException('Theme name must only contain alphabetical characters and begin with uppercase letter.');
        }

        $name = trim(preg_replace('#(?:Theme)?(?:App)?$#', '', $name));
        if (!empty($name)) {
            return $name;
        }
        throw new LogicException('Theme name is not valid.');
    }

    /**
     * @throws ThemeClassNotValidException
     */
    public function exists(): bool
    {
        if ($this->isProtected()) {
            return true;
        }
        if (
            $this->filesystem->exists($this->getThemePath())
            || $this->filesystem->exists($this->projectDir.'/vendor/roadiz/'.$this->getThemeName())
        ) {
            return true;
        }

        return false;
    }

    protected function getProtectedThemePath(): string
    {
        if ($this->filesystem->exists($this->projectDir.'/vendor/roadiz/'.$this->getThemeName())) {
            return $this->projectDir.'/vendor/roadiz/'.$this->getThemeName();
        } elseif ($this->filesystem->exists($this->projectDir.'/themes/'.$this->getThemeName())) {
            return $this->projectDir.'/themes/'.$this->getThemeName();
        }
        throw new \InvalidArgumentException($this->getThemeName().' does not exist in project and vendor.');
    }

    /**
     * Get real theme path from its name.
     *
     * Attention: theme could be located in vendor folder (/vendor/roadiz/roadiz)
     *
     * @return string theme absolute path
     *
     * @throws ThemeClassNotValidException
     */
    public function getThemePath(): string
    {
        if (null === $this->themePath) {
            if ($this->isProtected()) {
                $this->themePath = $this->getProtectedThemePath();
            } elseif ($this->isValid()) {
                $className = $this->getClassname();
                if (method_exists($className, 'getThemeFolder')) {
                    $this->themePath = $className::getThemeFolder();
                }
            } else {
                $this->themePath = $this->projectDir.'/themes/'.$this->getThemeName();
            }
        }

        return $this->themePath;
    }

    /**
     * @param class-string|null $className
     *
     * @throws ThemeClassNotValidException
     */
    public function getThemeReflectionClass(?string $className = null): ?\ReflectionClass
    {
        try {
            if (null === $className) {
                $className = $this->getClassname();
            }
            $reflection = new \ReflectionClass($className);
            if ($reflection->isSubclassOf(AbstractController::class)) {
                return $reflection;
            }
        } catch (\ReflectionException $Exception) {
            return null;
        }

        return null;
    }

    protected function getThemeNameFromName(): string
    {
        if (in_array($this->name, self::$protectedThemeNames)) {
            return $this->name;
        }

        return $this->name.'Theme';
    }

    /**
     * @return string Theme name WITHOUT suffix
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string Theme name WITH suffix
     */
    public function getThemeName(): string
    {
        return $this->themeName;
    }

    /**
     * @return class-string Theme class FQN
     *
     * @throws ThemeClassNotValidException
     */
    public function getClassname(): string
    {
        if (null === $this->classname) {
            $this->classname = $this->guessClassnameFromThemeName($this->getThemeName());
        }

        return $this->classname;
    }

    /**
     * @throws ThemeClassNotValidException
     */
    public function isValid(): bool
    {
        try {
            $className = $this->getClassname();
            $reflection = new \ReflectionClass($className);
            if ($reflection->isSubclassOf(AbstractController::class)) {
                return true;
            }
        } catch (\ReflectionException $Exception) {
            return false;
        }

        return false;
    }
}
