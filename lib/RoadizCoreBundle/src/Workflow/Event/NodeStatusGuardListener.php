<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Workflow\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

final readonly class NodeStatusGuardListener implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
    }

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
        if (!$this->security->isGranted(NodeVoter::EDIT_CONTENT, $event->getSubject())) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to edit this node.',
                '1'
            ));
        }
    }

    public function guardPublish(GuardEvent $event): void
    {
        if (!$this->security->isGranted(NodeVoter::EDIT_STATUS, $event->getSubject())) {
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
        if (!$this->security->isGranted(NodeVoter::EDIT_STATUS, $event->getSubject())) {
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
        if (!$this->security->isGranted(NodeVoter::DELETE, $event->getSubject())) {
            $event->addTransitionBlocker(new TransitionBlocker(
                'User is not allowed to delete this node.',
                '1'
            ));
        }
    }
}
