<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

final class NodeStatusGuardListener implements EventSubscriberInterface
{
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.node.guard' => ['guard'],
            'workflow.node.guard.publish' => ['guardPublish'],
            'workflow.node.guard.archive' => ['guardArchive'],
            'workflow.node.guard.delete' => ['guardDelete'],
        ];
    }

    public function guard(GuardEvent $event): void
    {
        if (!$this->security->isGranted('ROLE_ACCESS_NODES')) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to edit this node.',
                '1'
            ));
        }
    }

    public function guardPublish(GuardEvent $event): void
    {
        if (!$this->security->isGranted('ROLE_ACCESS_NODES_STATUS')) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to publish this node.',
                '1'
            ));
        }
    }

    public function guardArchive(GuardEvent $event): void
    {
        /** @var Node $node */
        $node = $event->getSubject();
        if ($node->isLocked()) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'A locked node cannot be archived.',
                '1'
            ));
        }
        if (!$this->security->isGranted('ROLE_ACCESS_NODES_STATUS')) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to archive this node.',
                '1'
            ));
        }
    }

    public function guardDelete(GuardEvent $event): void
    {
        /** @var Node $node */
        $node = $event->getSubject();
        if ($node->isLocked()) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'A locked node cannot be deleted.',
                '1'
            ));
        }
        if (!$this->security->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to delete this node.',
                '1'
            ));
        }
    }
}
