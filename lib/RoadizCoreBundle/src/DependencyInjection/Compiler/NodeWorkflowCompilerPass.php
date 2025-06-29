<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Workflow\NodesSourcesWorkflow;
use RZ\Roadiz\CoreBundle\Workflow\NodeWorkflow;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;

class NodeWorkflowCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('workflow.registry')) {
            throw new LogicException('Workflow support cannot be enabled as the Workflow component is not installed. Try running "composer require symfony/workflow".');
        }

        $registryDefinition = $container->getDefinition('workflow.registry');

        $nodeStrategy = new Definition(InstanceOfSupportStrategy::class, [NodeInterface::class]);
        $nodeStrategy->setPublic(false);
        $registryDefinition->addMethodCall('addWorkflow', [new Reference(NodeWorkflow::class), $nodeStrategy]);

        $nodesSourcesStrategy = new Definition(InstanceOfSupportStrategy::class, [NodesSources::class]);
        $nodesSourcesStrategy->setPublic(false);
        $registryDefinition->addMethodCall('addWorkflow', [new Reference(NodesSourcesWorkflow::class), $nodesSourcesStrategy]);
    }
}
