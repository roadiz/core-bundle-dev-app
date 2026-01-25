<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class NodeRouteHelper
{
    /**
     * @var class-string<object&callable>|null
     */
    private ?string $controller = null;

    /**
     * @param class-string<object&callable> $defaultControllerClass
     */
    public function __construct(
        private readonly NodeInterface $node,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly LoggerInterface $logger,
        private readonly string $defaultControllerClass,
        private readonly NodeTypes $nodeTypesBag,
        private readonly string $defaultControllerNamespace = '\\App\\Controller',
    ) {
    }

    /**
     * Get controller class path for a given node.
     *
     * @return class-string<object&callable>|null
     */
    public function getController(): ?string
    {
        if (null !== $this->controller) {
            return $this->controller;
        }

        $nodeType = $this->nodeTypesBag->get($this->node->getNodeTypeName()) ?? throw new \InvalidArgumentException('NodeType '.$this->node->getNodeTypeName().' does not exist.');
        if (!$nodeType->isReachable()) {
            return null;
        }
        $controllerClassName = $this->getControllerNamespace().'\\'.
            StringHandler::classify($nodeType->getName()).
            'Controller';

        if ($this->isCallable($controllerClassName)) {
            $this->controller = $controllerClassName;
        } else {
            /*
             * Use a default controller if no controller was found in Theme.
             */
            $this->controller = $this->defaultControllerClass;
        }

        return $this->controller;
    }

    /**
     * @phpstan-assert-if-true class-string<object&callable> $controllerClassName
     */
    private function isCallable(string $controllerClassName): bool
    {
        if (\class_exists($controllerClassName)) {
            $reflection = new \ReflectionClass($controllerClassName);

            if ($reflection->hasMethod('__invoke')) {
                return true;
            }
        }

        return false;
    }

    private function getControllerNamespace(): string
    {
        return $this->defaultControllerNamespace;
    }

    public function getMethod(): string
    {
        return '__invoke';
    }

    /**
     * Return FALSE or TRUE if node is viewable.
     */
    public function isViewable(): bool
    {
        $controller = $this->getController();
        if (null === $controller) {
            return false;
        }
        if (!class_exists($controller)) {
            $this->logger->debug($controller.' controller does not exist.');

            return false;
        }
        if (!method_exists($controller, $this->getMethod())) {
            $this->logger->debug(
                $controller.':'.
                $this->getMethod().' controller method does not exist.'
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
