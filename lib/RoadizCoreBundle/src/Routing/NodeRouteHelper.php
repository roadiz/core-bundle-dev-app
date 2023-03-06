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
     * @var class-string
     */
    private string $defaultControllerClass;
    /**
     * @var class-string|null
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
     * @return string
     */
    public function getController(): string
    {
        if (null === $this->controller) {
            $namespace = $this->getControllerNamespace();
            $this->controller = $namespace . '\\' .
                StringHandler::classify($this->node->getNodeType()->getName()) .
                'Controller';

            /*
             * Use a default controller if no controller was found in Theme.
             */
            if (!class_exists($this->controller) && $this->node->getNodeType()->isReachable()) {
                $this->controller = $this->defaultControllerClass;
            }
        }

        return $this->controller;
    }

    protected function getControllerNamespace(): string
    {
        $namespace = $this->defaultControllerNamespace;
        if (null !== $this->theme) {
            $refl = new \ReflectionClass($this->theme->getClassName());
            $namespace = $refl->getNamespaceName() . '\\Controllers';
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
     * @throws \ReflectionException
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
        /*
         * For archived and deleted nodes
         */
        if ($this->node->getStatus() > Node::PUBLISHED) {
            /*
             * Not allowed to see deleted and archived nodes
             * even for Admins
             */
            return false;
        }

        /*
         * For unpublished nodes
         */
        if ($this->node->getStatus() < Node::PUBLISHED) {
            if (true === $this->previewResolver->isPreview()) {
                return true;
            }
            /*
             * Not allowed to see unpublished nodes
             */
            return false;
        }

        /*
         * Everyone can view published nodes.
         */
        return true;
    }
}
