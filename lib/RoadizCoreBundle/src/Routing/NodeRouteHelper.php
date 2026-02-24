<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NodeRouteHelper
{
    /**
     * @var class-string<AbstractController>|null
     */
    private ?string $controller = null;

    /**
     * @param class-string<AbstractController> $defaultControllerClass
     */
    public function __construct(
        private readonly NodeInterface $node,
        private readonly ?Theme $theme,
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
     * @return class-string<AbstractController>|null
     */
    public function getController(): ?string
    {
        if (null === $this->controller) {
            $nodeType = $this->nodeTypesBag->get($this->node->getNodeTypeName());
            if (!$nodeType->isReachable()) {
                return null;
            }
            $controllerClassName = $this->getControllerNamespace().'\\'.
                StringHandler::classify($nodeType->getName()).
                'Controller';

            if (\class_exists($controllerClassName)) {
                $reflection = new \ReflectionClass($controllerClassName);
                if (!$reflection->isSubclassOf(AbstractController::class)) {
                    throw new \InvalidArgumentException('Controller class '.$controllerClassName.' must extends '.AbstractController::class);
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

    private function getControllerNamespace(): string
    {
        $namespace = $this->defaultControllerNamespace;
        if (null !== $this->theme) {
            $reflection = new \ReflectionClass($this->theme->getClassName());
            $namespace = $reflection->getNamespaceName().'\\Controllers';
        }

        return $namespace;
    }

    public function getMethod(): string
    {
        return 'indexAction';
    }

    /**
     * Return FALSE or TRUE if node is viewable.
     */
    public function isViewable(): bool
    {
        if (!class_exists($this->getController())) {
            $this->logger->debug($this->getController().' controller does not exist.');

            return false;
        }
        if (!method_exists($this->getController(), $this->getMethod())) {
            $this->logger->debug(
                $this->getController().':'.
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
