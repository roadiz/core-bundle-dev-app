<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NodeRouteHelper
{
    private Node $node;
    private ?Theme $theme;
    private PreviewResolverInterface $previewResolver;
    private LoggerInterface $logger;
    private string $defaultControllerNamespace;
    /**
     * @var class-string<AbstractController>
     */
    private string $defaultControllerClass;
    /**
     * @var class-string<AbstractController>|null
     */
    private ?string $controller = null;

    /**
     * @param Node $node
     * @param Theme|null $theme
     * @param PreviewResolverInterface $previewResolver
     * @param LoggerInterface $logger
     * @param class-string<AbstractController> $defaultControllerClass
     * @param string $defaultControllerNamespace
     */
    public function __construct(
        Node $node,
        ?Theme $theme,
        PreviewResolverInterface $previewResolver,
        LoggerInterface $logger,
        string $defaultControllerClass,
        string $defaultControllerNamespace = '\\App\\Controller'
    ) {
        $this->node = $node;
        $this->theme = $theme;
        $this->previewResolver = $previewResolver;
        $this->defaultControllerClass = $defaultControllerClass;
        $this->logger = $logger;
        $this->defaultControllerNamespace = $defaultControllerNamespace;
    }

    /**
     * Get controller class path for a given node.
     *
     * @return class-string<AbstractController>|null
     */
    public function getController(): ?string
    {
        if (null === $this->controller) {
            if (!$this->node->getNodeType()->isReachable()) {
                return null;
            }
            $controllerClassName = $this->getControllerNamespace() . '\\' .
                StringHandler::classify($this->node->getNodeType()->getName()) .
                'Controller';

            if (\class_exists($controllerClassName)) {
                $reflection = new \ReflectionClass($controllerClassName);
                if (!$reflection->isSubclassOf(AbstractController::class)) {
                    throw new \InvalidArgumentException(
                        'Controller class ' . $controllerClassName . ' must extends ' . AbstractController::class
                    );
                }
                // @phpstan-ignore-next-line
                $this->controller = $controllerClassName;
            } else {
                /*
                 * Use a default controller if no controller was found in Theme.
                 */
                $this->controller = $this->defaultControllerClass;
            }
        }
        // @phpstan-ignore-next-line
        return $this->controller;
    }

    protected function getControllerNamespace(): string
    {
        $namespace = $this->defaultControllerNamespace;
        if (null !== $this->theme) {
            $reflection = new \ReflectionClass($this->theme->getClassName());
            $namespace = $reflection->getNamespaceName() . '\\Controllers';
        }
        return $namespace;
    }

    public function getMethod(): string
    {
        return 'indexAction';
    }

    /**
     * Return FALSE or TRUE if node is viewable.
     *
     * @return bool
     */
    public function isViewable(): bool
    {
        if (!class_exists($this->getController())) {
            $this->logger->debug($this->getController() . ' controller does not exist.');
            return false;
        }
        if (!method_exists($this->getController(), $this->getMethod())) {
            $this->logger->debug(
                $this->getController() . ':' .
                $this->getMethod() . ' controller method does not exist.'
            );
            return false;
        }

        if ($this->previewResolver->isPreview()) {
            return $this->node->isDraft() || $this->node->isPending() || $this->node->isPublished();
        }

        /*
         * Everyone can view published nodes.
         */
        return $this->node->isPublished();
    }
}
