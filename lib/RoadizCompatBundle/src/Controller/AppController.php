<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Controller;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Exception\ThemeClassNotValidException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
     * @var Node|null
     */
    private ?Node $homeNode = null;

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
     * @return RouteCollection
     * @throws ReflectionException
     */
    public static function getRoutes(): RouteCollection
    {
        $locator = static::getFileLocator();
        $loader = new YamlFileLoader($locator);
        return $loader->load('routes.yml');
    }

    /**
     * Return a file locator with theme
     * Resource folder.
     *
     * @return FileLocator
     * @throws ReflectionException
     */
    public static function getFileLocator(): FileLocator
    {
        $resourcesFolder = static::getResourcesFolder();
        return new FileLocator([
            $resourcesFolder,
            $resourcesFolder . '/routing',
            $resourcesFolder . '/config',
        ]);
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
     * These routes are used to extend Roadiz back-office.
     *
     * @return RouteCollection|null
     * @throws ReflectionException
     */
    public static function getBackendRoutes(): ?RouteCollection
    {
        $locator = static::getFileLocator();

        try {
            $loader = new YamlFileLoader($locator);
            return $loader->load('backend-routes.yml');
        } catch (InvalidArgumentException $e) {
            return null;
        }
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
     */
    public function prepareBaseAssignation()
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->get('kernel');
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
    public function throw404($message = '')
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
     */
    public function getTheme(): ?Theme
    {
        $this->getStopwatch()->start('getTheme');
        /** @var ThemeResolverInterface $themeResolver */
        $themeResolver = $this->get(ThemeResolverInterface::class);
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
     * Validate a request against a given ROLE_*
     * and check chroot
     * and throws an AccessDeniedException exception.
     *
     * @param mixed $attributes
     * @param mixed $nodeId
     * @param bool|false $includeChroot
     * @return void
     *
     * @throws AccessDeniedException
     * @deprecated Use denyAccessUnlessGranted with NodeVoter attribute and a Node subject.
     */
    public function validateNodeAccessForRole(mixed $attributes, mixed $nodeId = null, bool $includeChroot = false): void
    {
        /** @var Node|null $node */
        $node = null;
        /** @var User $user */
        $user = $this->getUser();
        /** @var NodeChrootResolver $chrootResolver */
        $chrootResolver = $this->get(NodeChrootResolver::class);
        $chroot = $chrootResolver->getChroot($user);

        if ($this->isGranted($attributes) && $chroot === null) {
            /*
             * Already grant access if user is not chroot-ed.
             */
            return;
        }

        if ($nodeId instanceof Node) {
            $node = $nodeId;
        } elseif (\is_scalar($nodeId)) {
            /** @var Node|null $node */
            $node = $this->em()->find(Node::class, (int) $nodeId);
        }

        if (null === $node) {
            throw $this->createAccessDeniedException("You don't have access to this page");
        }

        $this->em()->refresh($node);

        /** @var NodeHandler $nodeHandler */
        $nodeHandler = $this->getHandlerFactory()->getHandler($node);
        $parents = $nodeHandler->getParents();

        if ($includeChroot) {
            $parents[] = $node;
        }

        if (!$this->isGranted($attributes)) {
            throw $this->createAccessDeniedException("You don't have access to this page");
        }

        if (null !== $user && $chroot !== null && !in_array($chroot, $parents, true)) {
            throw $this->createAccessDeniedException("You don't have access to this page");
        }
    }

    /**
     * Generate a simple view to inform visitors that website is
     * currently unavailable.
     *
     * @param Request $request
     * @return Response
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
     */
    public function makeResponseCachable(
        Request $request,
        Response $response,
        int $minutes,
        bool $allowClientCache = false
    ): Response {
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        /** @var RequestStack $requestStack */
        $requestStack = $this->get(RequestStack::class);
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

    /**
     * Returns a fully qualified view path for Twig rendering.
     *
     * @param string $view
     * @param string $namespace
     * @return string
     */
    protected function getNamespacedView(string $view, string $namespace = ''): string
    {
        if ($namespace !== "" && $namespace !== "/") {
            $view = '@' . $namespace . '/' . $view;
        } elseif (static::getThemeDir() !== "" && $namespace !== "/") {
            // when no namespace is used
            // use current theme directory
            $view = '@' . static::getThemeDir() . '/' . $view;
        }

        return $view;
    }

    /**
     * @param TranslationInterface|null $translation
     * @return null|Node
     */
    protected function getHome(?TranslationInterface $translation = null): ?Node
    {
        $this->getStopwatch()->start('getHome');
        if (null === $this->homeNode) {
            $nodeRepository = $this->em()->getRepository(Node::class);
            if ($translation !== null) {
                $this->homeNode = $nodeRepository->findHomeWithTranslation($translation);
            } else {
                $this->homeNode = $nodeRepository->findHomeWithDefaultTranslation();
            }
        }
        $this->getStopwatch()->stop('getHome');

        return $this->homeNode;
    }

    /**
     * Return all Form errors as an array.
     *
     * @param FormInterface $form
     * @return array
     * @deprecated Use FormErrorSerializer::getErrorsAsArray instead
     */
    protected function getErrorsAsArray(FormInterface $form): array
    {
        /** @var FormErrorSerializer $formErrorSerializer */
        $formErrorSerializer = $this->get(FormErrorSerializer::class);
        return $formErrorSerializer->getErrorsAsArray($form);
    }
}
