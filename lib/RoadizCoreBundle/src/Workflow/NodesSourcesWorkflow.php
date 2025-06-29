<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow;

use RZ\Roadiz\CoreBundle\Workflow\MarkingStore\StatusAwareEntityMarkingStore;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class NodesSourcesWorkflow extends Workflow
{
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder
            ->setInitialPlaces(StatusAwareEntityMarkingStore::DRAFT)
            ->addPlaces([
                StatusAwareEntityMarkingStore::DRAFT,
                StatusAwareEntityMarkingStore::PUBLISHED,
                StatusAwareEntityMarkingStore::DELETED,
            ])
            ->addTransition(new Transition('reject', StatusAwareEntityMarkingStore::PUBLISHED, StatusAwareEntityMarkingStore::DRAFT))
            ->addTransition(new Transition('publish', StatusAwareEntityMarkingStore::DRAFT, StatusAwareEntityMarkingStore::PUBLISHED))
            ->addTransition(new Transition('publish', StatusAwareEntityMarkingStore::PUBLISHED, StatusAwareEntityMarkingStore::PUBLISHED))
            ->addTransition(new Transition('delete', StatusAwareEntityMarkingStore::DRAFT, StatusAwareEntityMarkingStore::DELETED))
            ->addTransition(new Transition('delete', StatusAwareEntityMarkingStore::PUBLISHED, StatusAwareEntityMarkingStore::DELETED))
            ->addTransition(new Transition('undelete', StatusAwareEntityMarkingStore::DELETED, StatusAwareEntityMarkingStore::DRAFT))
            ->build()
        ;
        $markingStore = new StatusAwareEntityMarkingStore();
        parent::__construct($definition, $markingStore, $dispatcher, 'nodesSources');
    }
}
