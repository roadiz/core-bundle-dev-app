<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow;

use RZ\Roadiz\CoreBundle\Entity\Node;
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
            ->setInitialPlaces($this->toPlace(Node::DRAFT))
            ->addPlaces([
                $this->toPlace(Node::DRAFT),
                $this->toPlace(Node::PENDING),
                $this->toPlace(Node::PUBLISHED),
                $this->toPlace(Node::ARCHIVED),
                $this->toPlace(Node::DELETED),
            ])
            ->addTransition(new Transition('review', $this->toPlace(Node::DRAFT), $this->toPlace(Node::PENDING)))
            ->addTransition(new Transition('review', $this->toPlace(Node::PUBLISHED), $this->toPlace(Node::PENDING)))
            ->addTransition(new Transition('reject', $this->toPlace(Node::PENDING), $this->toPlace(Node::DRAFT)))
            ->addTransition(new Transition('reject', $this->toPlace(Node::PUBLISHED), $this->toPlace(Node::DRAFT)))
            ->addTransition(new Transition('publish', $this->toPlace(Node::DRAFT), $this->toPlace(Node::PUBLISHED)))
            ->addTransition(new Transition('publish', $this->toPlace(Node::PENDING), $this->toPlace(Node::PUBLISHED)))
            ->addTransition(new Transition('publish', $this->toPlace(Node::PUBLISHED), $this->toPlace(Node::PUBLISHED)))
            ->addTransition(new Transition('archive', $this->toPlace(Node::PUBLISHED), $this->toPlace(Node::ARCHIVED)))
            ->addTransition(new Transition('unarchive', $this->toPlace(Node::ARCHIVED), $this->toPlace(Node::DRAFT)))
            ->addTransition(new Transition('delete', $this->toPlace(Node::DRAFT), $this->toPlace(Node::DELETED)))
            ->addTransition(new Transition('delete', $this->toPlace(Node::PENDING), $this->toPlace(Node::DELETED)))
            ->addTransition(new Transition('delete', $this->toPlace(Node::PUBLISHED), $this->toPlace(Node::DELETED)))
            ->addTransition(new Transition('delete', $this->toPlace(Node::ARCHIVED), $this->toPlace(Node::DELETED)))
            ->addTransition(new Transition('undelete', $this->toPlace(Node::DELETED), $this->toPlace(Node::DRAFT)))
            ->build()
        ;
        $markingStore = new MethodMarkingStore(true, 'status');
        parent::__construct($definition, $markingStore, $dispatcher, 'node');
    }

    protected function toPlace(int $legacyPlace): string
    {
        return (string) $legacyPlace;
    }
}
