<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeApi;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\Roadiz\CoreBundle\Exception\ForceResponseException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManager;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\MediaFinders\RandomImageFinder;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\RuntimeError;

/**
 * @deprecated Use AbstractController instead
 */
abstract class Controller extends AbstractController
{
    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'csrfTokenManager' => CsrfTokenManagerInterface::class,
            'defaultTranslation' => 'defaultTranslation',
            'dispatcher' => 'event_dispatcher',
            'doctrine' => 'doctrine',
            'em' => EntityManagerInterface::class,
            'event_dispatcher' => 'event_dispatcher',
            EventDispatcherInterface::class => EventDispatcherInterface::class,
            LogTrail::class => LogTrail::class,
            'kernel' => KernelInterface::class,
            'logger' => LoggerInterface::class,
            'nodeApi' => NodeApi::class,
            'nodeSourceApi' => NodeSourceApi::class,
            'nodeTypesBag' => NodeTypes::class,
            'requestStack' => RequestStack::class,
            'securityAuthenticationUtils' => AuthenticationUtils::class,
            'securityTokenStorage' => TokenStorageInterface::class,
            'settingsBag' => Settings::class,
            'stopwatch' => Stopwatch::class,
            'translator' => TranslatorInterface::class,
            'urlGenerator' => UrlGeneratorInterface::class,
            UrlGeneratorInterface::class => UrlGeneratorInterface::class,
            DocumentUrlGeneratorInterface::class => DocumentUrlGeneratorInterface::class,
            Environment::class => Environment::class,
            FormErrorSerializer::class => FormErrorSerializer::class,
            LoggerInterface::class => LoggerInterface::class,
            NodeChrootResolver::class => NodeChrootResolver::class,
            NodeFactory::class => NodeFactory::class,
            OAuth2LinkGenerator::class => OAuth2LinkGenerator::class,
            PreviewResolverInterface::class => PreviewResolverInterface::class,
            RandomImageFinder::class => RandomImageFinder::class,
            RendererInterface::class => RendererInterface::class,
            RequestStack::class => RequestStack::class,
            Security::class => Security::class,
            Settings::class => Settings::class,
            Stopwatch::class => Stopwatch::class,
            TokenStorageInterface::class => TokenStorageInterface::class,
            TranslatorInterface::class => TranslatorInterface::class,
            FormFactoryInterface::class => FormFactoryInterface::class,
            HandlerFactoryInterface::class => HandlerFactoryInterface::class,
        ]);
    }

    /**
     * @deprecated
     */
    protected function getRequest(): Request
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get(RequestStack::class);
        $request = $requestStack->getCurrentRequest();
        if (null === $request) {
            throw new BadRequestHttpException('Request is not available in this context');
        }

        return $request;
    }

    /**
     * Alias for `$this->container['securityTokenStorage']`.
     *
     * @deprecated
     */
    protected function getTokenStorage(): TokenStorageInterface
    {
        /** @var TokenStorageInterface $tokenStorage */ // php-stan hint
        $tokenStorage = $this->container->get(TokenStorageInterface::class);

        return $tokenStorage;
    }

    /**
     * Alias for `$this->container['em']`.
     *
     * @deprecated
     */
    protected function em(): ObjectManager
    {
        return $this->container->get('em');
    }

    /**
     * @deprecated
     */
    protected function getTranslator(): TranslatorInterface
    {
        /** @var TranslatorInterface $translator */ // php-stan hint
        $translator = $this->container->get(TranslatorInterface::class);

        return $translator;
    }

    /**
     * @deprecated
     */
    protected function getTwig(): Environment
    {
        /** @var Environment $twig */ // php-stan hint
        $twig = $this->container->get(Environment::class);

        return $twig;
    }

    /**
     * @deprecated
     */
    protected function getStopwatch(): Stopwatch
    {
        /** @var Stopwatch $stopwatch */
        $stopwatch = $this->container->get(Stopwatch::class);

        return $stopwatch;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @deprecated
     */
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->container->get('doctrine');
    }

    /**
     * @return object The passed $event MUST be returned
     *
     * @deprecated
     */
    protected function dispatchEvent(object $event, ?string $eventName = null): object
    {
        /** @var EventDispatcherInterface $eventDispatcher */ // php-stan hint
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        return $eventDispatcher->dispatch($event, $eventName);
    }

    /**
     * @deprecated
     */
    protected function getSettingsBag(): Settings
    {
        /** @var Settings $settingsBag */ // php-stan hint
        $settingsBag = $this->container->get(Settings::class);

        return $settingsBag;
    }

    /**
     * @deprecated
     */
    protected function getLogger(): LoggerInterface
    {
        /** @var LoggerInterface $logger */ // php-stan hint
        $logger = $this->container->get(LoggerInterface::class);

        return $logger;
    }

    /**
     * Wrap `$this->get('urlGenerator')->generate`.
     *
     * @param string|NodesSources $route
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    protected function generateUrl($route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if ($route instanceof NodesSources) {
            /** @var UrlGeneratorInterface $urlGenerator */
            $urlGenerator = $this->container->get(UrlGeneratorInterface::class);

            return $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                array_merge($parameters, [RouteObjectInterface::ROUTE_OBJECT => $route]),
                $referenceType
            );
        }

        return parent::generateUrl($route, $parameters, $referenceType);
    }

    /**
     * @return class-string
     */
    public static function getCalledClass(): string
    {
        $className = static::class;
        if (!str_starts_with($className, '\\')) {
            $className = '\\'.$className;
        }

        // @phpstan-ignore-next-line
        return $className;
    }

    /**
     * Custom route for redirecting routes with a trailing slash.
     */
    public function removeTrailingSlashAction(Request $request): RedirectResponse
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Return a Response from a template string with its rendering assignation.
     *
     * @see http://api.symfony.com/2.6/Symfony/Bundle/FrameworkBundle/Controller/Controller.html#method_render
     *
     * @param string        $view       Template file path
     * @param array         $parameters Twig assignation array
     * @param Response|null $response   Optional Response object to customize response parameters
     * @param string        $namespace  Twig loader namespace
     *
     * @throws RuntimeError
     */
    #[\Override]
    public function render(string $view, array $parameters = [], ?Response $response = null, string $namespace = ''): Response
    {
        try {
            return parent::render($view, $parameters, $response);
        } catch (RuntimeError $e) {
            if ($e->getPrevious() instanceof ForceResponseException) {
                return $e->getPrevious()->getResponse();
            } else {
                throw $e;
            }
        }
    }

    protected function getNamespacedView(string $view, string $namespace = ''): string
    {
        if ('' !== $namespace && '/' !== $namespace) {
            return '@'.$namespace.'/'.$view;
        }

        return $view;
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @param string $name    Form name
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createNamedFormBuilder(string $name = 'form', mixed $data = null, array $options = []): FormBuilderInterface
    {
        /** @var FormFactoryInterface $formFactory */
        $formFactory = $this->container->get(FormFactoryInterface::class);

        return $formFactory->createNamedBuilder($name, FormType::class, $data, $options);
    }

    /**
     * Creates and returns an EntityListManager instance.
     *
     * @param class-string<PersistableInterface> $entity Entity class path
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createEntityListManager(string $entity, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return new EntityListManager(
            $this->getRequest(),
            $this->getDoctrine()->getManager(),
            $entity,
            $criteria,
            $ordering
        );
    }

    /**
     * Get a user from the tokenStorage.
     *
     * @throws \LogicException If tokenStorage is not available
     *
     * @see TokenInterface::getUser()
     */
    #[\Override]
    protected function getUser(): ?UserInterface
    {
        /** @var TokenInterface|null $token */
        $token = $this->getTokenStorage()->getToken();

        return $token?->getUser();
    }
}
