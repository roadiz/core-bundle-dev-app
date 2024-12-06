<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow;

use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NodeWorkflow extends Workflow
{
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder
            ->setInitialPlaces(NodeStatus::DRAFT->name)
            ->addPlaces([
                NodeStatus::DRAFT->name,
                NodeStatus::PENDING->name,
                NodeStatus::PUBLISHED->name,
                NodeStatus::ARCHIVED->name,
                NodeStatus::DELETED->name,
            ])
            ->addTransition(new Transition('review', NodeStatus::DRAFT->name, NodeStatus::PENDING->name))
            ->addTransition(new Transition('review', NodeStatus::PUBLISHED->name, NodeStatus::PENDING->name))
            ->addTransition(new Transition('reject', NodeStatus::PENDING->name, NodeStatus::DRAFT->name))
            ->addTransition(new Transition('reject', NodeStatus::PUBLISHED->name, NodeStatus::DRAFT->name))
            ->addTransition(new Transition('publish', NodeStatus::DRAFT->name, NodeStatus::PUBLISHED->name))
            ->addTransition(new Transition('publish', NodeStatus::PENDING->name, NodeStatus::PUBLISHED->name))
            ->addTransition(new Transition('publish', NodeStatus::PUBLISHED->name, NodeStatus::PUBLISHED->name))
            ->addTransition(new Transition('archive', NodeStatus::PUBLISHED->name, NodeStatus::ARCHIVED->name))
            ->addTransition(new Transition('unarchive', NodeStatus::ARCHIVED->name, NodeStatus::DRAFT->name))
            ->addTransition(new Transition('delete', NodeStatus::DRAFT->name, NodeStatus::DELETED->name))
            ->addTransition(new Transition('delete', NodeStatus::PENDING->name, NodeStatus::DELETED->name))
            ->addTransition(new Transition('delete', NodeStatus::PUBLISHED->name, NodeStatus::DELETED->name))
            ->addTransition(new Transition('delete', NodeStatus::ARCHIVED->name, NodeStatus::DELETED->name))
            ->addTransition(new Transition('undelete', NodeStatus::DELETED->name, NodeStatus::DRAFT->name))
            ->build()
        ;
        $markingStore = new MethodMarkingStore(true, 'statusAsString');
        parent::__construct($definition, $markingStore, $dispatcher, 'node');
    }
}
