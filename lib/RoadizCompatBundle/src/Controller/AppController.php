<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Exception\ThemeClassNotValidException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\UnicodeString;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Base class for Roadiz themes.
 */
abstract class AppController extends Controller
{
    public const AJAX_TOKEN_INTENTION = 'ajax';
    public const SCHEMA_TOKEN_INTENTION = 'update_schema';

    /**
     * @var int Theme priority to load templates and translation in the right order.
     */
    public static int $priority = 0;
    /**
     * Theme name.
     *
     * @var string
     */
    protected static string $themeName = '';
    /**
     * Theme author description.
     *
     * @var string
     */
    protected static string $themeAuthor = '';
    /**
     * Theme copyright licence.
     *
     * @var string
     */
    protected static string $themeCopyright = '';
    /**
     * Theme base directory name.
     *
     * Example: "MyTheme" will be located in "themes/MyTheme"
     * @var string
     */
    protected static string $themeDir = '';
    /**
     * Theme requires a minimal CMS version.
     *
     * Example: "*" will accept any CMS version. Or "3.0.*" will
     * accept any build version of 3.0.
     *
     * @var string
     */
    protected static string $themeRequire = '*';
    /**
     * Is theme for backend?
     *
     * @var bool
     */
    protected static bool $backendTheme = false;
    protected ?Theme $theme = null;
    /**
     * Assignation for twig template engine.
     */
    protected array $assignation = [];

    /**
     * @return string
     */
    public static function getThemeName(): string
    {
        return static::$themeName;
    }

    /**
     * @return string
     */
    public static function getThemeAuthor(): string
    {
        return static::$themeAuthor;
    }

    /**
     * @return string
     */
    public static function getThemeCopyright(): string
    {
        return static::$themeCopyright;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return static::$priority;
    }

    /**
     * @return string
     */
    public static function getThemeRequire(): string
    {
        return static::$themeRequire;
    }

    /**
     * @return boolean
     */
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
     * @return string
     * @throws ReflectionException
     * @throws ThemeClassNotValidException
     */
    public static function getResourcesFolder(): string
    {
        return static::getThemeFolder() . '/Resources';
    }

    /**
     * Return theme root folder.
     *
     * @return string
     * @throws ReflectionException|ThemeClassNotValidException
     */
    public static function getThemeFolder(): string
    {
        $class_info = new ReflectionClass(static::getThemeMainClass());
        if (false === $themeFilename = $class_info->getFileName()) {
            throw new ThemeClassNotValidException('Theme class file is not valid or does not exist');
        }
        return dirname($themeFilename);
    }

    /**
     * @return class-string Main theme class (FQN class with namespace)
     * @throws ThemeClassNotValidException
     */
    public static function getThemeMainClass(): string
    {
        $mainClassName = '\\Themes\\' . static::getThemeDir() . '\\' . static::getThemeMainClassName();
        if (!class_exists($mainClassName)) {
            throw new ThemeClassNotValidException(sprintf('%s class does not exist', $mainClassName));
        }
        return $mainClassName;
    }

    /**
     * @return string
     */
    public static function getThemeDir(): string
    {
        return static::$themeDir;
    }

    /**
     * @return string Main theme class name
     */
    public static function getThemeMainClassName(): string
    {
        return static::getThemeDir() . 'App';
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public static function getTranslationsFolder(): string
    {
        return static::getResourcesFolder() . '/translations';
    }

    /**
     * @return string
     * @throws ReflectionException|ThemeClassNotValidException
     */
    public static function getPublicFolder(): string
    {
        return static::getThemeFolder() . '/static';
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public static function getViewsFolder(): string
    {
        return static::getResourcesFolder() . '/views';
    }

    /**
     * @return array
     */
    public function getAssignation(): array
    {
        return $this->assignation;
    }

    /**
     * Prepare base information to be rendered in twig templates.
     *
     * ## Available contents
     *
     * - request: Main request object
     * - head
     *     - ajax: `boolean`
     *     - cmsVersion
     *     - cmsVersionNumber
     *     - cmsBuild
     *     - devMode: `boolean`
     *     - baseUrl
     *     - filesUrl
     *     - resourcesUrl
     *     - absoluteResourcesUrl
     *     - staticDomainName
     *     - ajaxToken
     *     - fontToken
     *     - universalAnalyticsId
     * - session
     *     - messages
     *     - id
     *     - user
     * - bags
     *     - nodeTypes (ParametersBag)
     *     - settings (ParametersBag)
     *     - roles (ParametersBag)
     * - securityAuthorizationChecker
     *
     * @return $this
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function prepareBaseAssignation(): static
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        $this->assignation = [
            'head' => [
                'ajax' => $this->getRequest()->isXmlHttpRequest(),
                'devMode' => $kernel->isDebug(),
                'maintenanceMode' => (bool) $this->getSettingsBag()->get('maintenance_mode'),
                'baseUrl' => $this->getRequest()->getSchemeAndHttpHost() . $this->getRequest()->getBasePath(),
            ]
        ];

        return $this;
    }

    /**
     * Return a Response with default backend 404 error page.
     *
     * @param string $message Additional message to describe 404 error.
     *
     * @return Response
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
     * Return the current Theme
     *
     * @return Theme|null
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
                if ($className === false) {
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
     * @param Request $request
     * @param string $msg
     * @param object|null $source
     */
    public function publishConfirmMessage(Request $request, string $msg, ?object $source = null): void
    {
        $this->publishMessage($request, $msg, 'confirm', $source);
    }

    /**
     * Publish a message in Session flash bag and
     * logger interface.
     *
     * @param Request $request
     * @param string $msg
     * @param string $level
     * @param object|null $source
     */
    protected function publishMessage(
        Request $request,
        string $msg,
        string $level = "confirm",
        ?object $source = null
    ): void {
        $session = $this->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add($level, $msg);
        }

        switch ($level) {
            case 'error':
            case 'danger':
            case 'fail':
                $this->getLogger()->error($msg, ['entity' => $source]);
                break;
            default:
                $this->getLogger()->info($msg, ['entity' => $source]);
                break;
        }
    }

    /**
     * Returns the current session.
     *
     * @return SessionInterface|null
     */
    public function getSession(): ?SessionInterface
    {
        $request = $this->getRequest();
        return $request->hasPreviousSession() ? $request->getSession() : null;
    }

    /**
     * Publish an error message in Session flash bag and
     * logger interface.
     *
     * @param Request $request
     * @param string $msg
     * @param object|null $source
     * @return void
     */
    public function publishErrorMessage(Request $request, string $msg, ?object $source = null): void
    {
        $this->publishMessage($request, $msg, 'error', $source);
    }

    /**
     * Generate a simple view to inform visitors that website is
     * currently unavailable.
     *
     * @param Request $request
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function maintenanceAction(Request $request): Response
    {
        $this->prepareBaseAssignation();

        return new Response(
            $this->renderView('maintenance.html.twig', $this->assignation),
            Response::HTTP_SERVICE_UNAVAILABLE,
            ['content-type' => 'text/html']
        );
    }

    /**
     * Make current response cacheable by reverse proxy and browsers.
     *
     * Pay attention that, some reverse proxies systems will need to remove your response
     * cookies header to actually save your response.
     *
     * Do not cache, if
     * - we are in preview mode
     * - we are in debug mode
     * - Request forbids cache
     * - we are in maintenance mode
     * - this is a sub-request
     *
     * @param Request $request
     * @param Response $response
     * @param int $minutes TTL in minutes
     * @param bool $allowClientCache Allows browser level cache
     *
     * @return Response
     * @deprecated Use stateless routes and cache-control headers in your controllers
     */
    public function makeResponseCachable(
        Request $request,
        Response $response,
        int $minutes,
        bool $allowClientCache = false
    ): Response {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);
        $settings = $this->getSettingsBag();

        if (
            !$this->getPreviewResolver()->isPreview() &&
            !$kernel->isDebug() &&
            $requestStack->getMainRequest() === $request &&
            $request->isMethodCacheable() &&
            $minutes > 0 &&
            !$settings->get('maintenance_mode', false)
        ) {
            header_remove('Cache-Control');
            header_remove('Vary');
            $response->headers->remove('cache-control');
            $response->headers->remove('vary');
            $response->setPublic();
            $response->setSharedMaxAge(60 * $minutes);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            if ($allowClientCache) {
                $response->setMaxAge(60 * $minutes);
            }

            $response->setVary('Accept-Encoding, X-Partial, x-requested-with');

            if ($request->isXmlHttpRequest()) {
                $response->headers->add([
                    'X-Partial' => true
                ]);
            }
        }

        return $response;
    }
}
