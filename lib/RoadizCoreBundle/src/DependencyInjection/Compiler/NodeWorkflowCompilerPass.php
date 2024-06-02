<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\NodeTreeDto;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;

class NodeWorkflowCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('workflow.registry')) {
            throw new LogicException('Workflow support cannot be enabled as the Workflow component is not installed. Try running "composer require symfony/workflow".');
        }

        $workflowId = 'state_machine.node';
        $registryDefinition = $container->getDefinition('workflow.registry');

        $strategyDefinition = new Definition(InstanceOfSupportStrategy::class, [Node::class]);
        $strategyDefinition->setPublic(false);
        $dtoStrategyDefinition = new Definition(InstanceOfSupportStrategy::class, [NodeTreeDto::class]);
        $dtoStrategyDefinition->setPublic(false);
        $registryDefinition->addMethodCall('addWorkflow', [new Reference($workflowId), $strategyDefinition]);
        $registryDefinition->addMethodCall('addWorkflow', [new Reference($workflowId), $dtoStrategyDefinition]);
    }
}
