<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Exception\ThemeClassNotValidException;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\String\UnicodeString;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Base class for Roadiz themes.
 *
 * @deprecated Use AbstractController instead
 */
abstract class AppController extends Controller
{
    /**
     * @deprecated
     */
    public const AJAX_TOKEN_INTENTION = 'ajax';

    /**
     * @var int theme priority to load templates and translation in the right order
     */
    public static int $priority = 0;
    /**
     * Theme name.
     */
    protected static string $themeName = '';
    /**
     * Theme author description.
     */
    protected static string $themeAuthor = '';
    /**
     * Theme copyright licence.
     */
    protected static string $themeCopyright = '';
    /**
     * Theme base directory name.
     *
     * Example: "MyTheme" will be located in "themes/MyTheme"
     */
    protected static string $themeDir = '';
    /**
     * Theme requires a minimal CMS version.
     *
     * Example: "*" will accept any CMS version. Or "3.0.*" will
     * accept any build version of 3.0.
     */
    protected static string $themeRequire = '*';
    /**
     * Is theme for backend?
     */
    protected static bool $backendTheme = false;
    protected ?Theme $theme = null;
    /**
     * Assignation for twig template engine.
     */
    protected array $assignation = [];

    public static function getThemeName(): string
    {
        return static::$themeName;
    }

    public static function getThemeAuthor(): string
    {
        return static::$themeAuthor;
    }

    public static function getThemeCopyright(): string
    {
        return static::$themeCopyright;
    }

    public static function getPriority(): int
    {
        return static::$priority;
    }

    public static function getThemeRequire(): string
    {
        return static::$themeRequire;
    }

    public static function isBackendTheme(): bool
    {
        return static::$backendTheme;
    }

    /**
     * Return theme Resource folder according to
     * main theme class inheriting AppController.
     *
     * Uses \ReflectionClass to resolve final theme class folder
     * whether it’s located in project folder or in vendor folder.
     *
     * @throws \ReflectionException
     * @throws ThemeClassNotValidException
     */
    public static function getResourcesFolder(): string
    {
        return static::getThemeFolder().'/Resources';
    }

    /**
     * Return theme root folder.
     *
     * @throws \ReflectionException|ThemeClassNotValidException
     */
    public static function getThemeFolder(): string
    {
        $class_info = new \ReflectionClass(static::getThemeMainClass());
        if (false === $themeFilename = $class_info->getFileName()) {
            throw new ThemeClassNotValidException('Theme class file is not valid or does not exist');
        }

        return dirname($themeFilename);
    }

    /**
     * @return class-string Main theme class (FQN class with namespace)
     *
     * @throws ThemeClassNotValidException
     */
    public static function getThemeMainClass(): string
    {
        $mainClassName = '\\Themes\\'.static::getThemeDir().'\\'.static::getThemeMainClassName();
        if (!class_exists($mainClassName)) {
            throw new ThemeClassNotValidException(sprintf('%s class does not exist', $mainClassName));
        }

        return $mainClassName;
    }

    public static function getThemeDir(): string
    {
        return static::$themeDir;
    }

    /**
     * @return string Main theme class name
     */
    public static function getThemeMainClassName(): string
    {
        return static::getThemeDir().'App';
    }

    /**
     * @throws \ReflectionException|ThemeClassNotValidException
     */
    public static function getTranslationsFolder(): string
    {
        return static::getResourcesFolder().'/translations';
    }

    /**
     * @throws \ReflectionException|ThemeClassNotValidException
     */
    public static function getPublicFolder(): string
    {
        return static::getThemeFolder().'/static';
    }

    /**
     * @throws \ReflectionException|ThemeClassNotValidException
     */
    public static function getViewsFolder(): string
    {
        return static::getResourcesFolder().'/views';
    }

    public function getAssignation(): array
    {
        return $this->assignation;
    }

    /**
     * Prepare base information to be rendered in twig templates.
     *
     * @return $this
     *
     * @deprecated Use direct assignation in your controller actions and Twig extensions
     */
    public function prepareBaseAssignation(): static
    {
        return $this;
    }

    /**
     * Return a Response with default backend 404 error page.
     *
     * @param string $message additional message to describe 404 error
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function throw404(string $message = ''): Response
    {
        $this->assignation['nodeName'] = 'error-404';
        $this->assignation['nodeTypeName'] = 'error404';
        $this->assignation['errorMessage'] = $message;
        $this->assignation['title'] = $this->getTranslator()->trans('error404.title');
        $this->assignation['content'] = $this->getTranslator()->trans('error404.message');

        return new Response(
            $this->getTwig()->render('404.html.twig', $this->assignation),
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/html']
        );
    }

    /**
     * Return the current Theme.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTheme(): ?Theme
    {
        $this->getStopwatch()->start('getTheme');
        /** @var ThemeResolverInterface $themeResolver */
        $themeResolver = $this->container->get(ThemeResolverInterface::class);
        if (null === $this->theme) {
            $className = new UnicodeString(static::getCalledClass());
            while (!$className->endsWith('App')) {
                $className = get_parent_class($className->toString());
                if (false === $className) {
                    $className = new UnicodeString('');
                    break;
                }
                $className = new UnicodeString($className);
            }
            $this->theme = $themeResolver->findThemeByClass($className->toString());
        }
        $this->getStopwatch()->stop('getTheme');

        return $this->theme;
    }

    /**
     * Publish a confirmation message in Session flash bag and
     * logger interface.
     *
     * @deprecated Use LogTrail instead
     */
    public function publishConfirmMessage(Request $request, string $msg, ?object $source = null): void
    {
        $this->container->get(LogTrail::class)->publishConfirmMessage($request, $msg, $source);
    }

    /**
     * Returns the current session.
     */
    public function getSession(?Request $request = null): ?SessionInterface
    {
        $request = $request ?? $this->getRequest();

        return $request->hasPreviousSession() ? $request->getSession() : null;
    }

    /**
     * Publish an error message in Session flash bag and
     * logger interface.
     *
     * @deprecated Use LogTrail instead
     */
    public function publishErrorMessage(Request $request, string $msg, ?object $source = null): void
    {
        $this->container->get(LogTrail::class)->publishErrorMessage($request, $msg, $source);
    }

    /**
     * Generate a simple view to inform visitors that website is
     * currently unavailable.
     */
    public function maintenanceAction(Request $request): Response
    {
        return new Response(
            $this->renderView('maintenance.html.twig', $this->assignation),
            Response::HTTP_SERVICE_UNAVAILABLE,
            ['content-type' => 'text/html']
        );
    }
}
