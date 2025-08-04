<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'CREATE'|'DUPLICATE'|'CREATE_AT_ROOT'|'SEARCH'|'READ'|'READ_AT_ROOT'|'EMPTY_TRASH'|'READ_LOGS'|'EDIT_CONTENT'|'EDIT_TAGS'|'EDIT_REALMS'|'EDIT_SETTING'|'EDIT_STATUS'|'EDIT_ATTRIBUTE'|'DELETE', Node>
 */
final class NodeVoter extends Voter
{
    public const string CREATE = 'CREATE';
    public const string DUPLICATE = 'DUPLICATE';
    public const string CREATE_AT_ROOT = 'CREATE_AT_ROOT';
    public const string SEARCH = 'SEARCH';
    public const string READ = 'READ';
    public const string READ_AT_ROOT = 'READ_AT_ROOT';
    public const string EMPTY_TRASH = 'EMPTY_TRASH';
    public const string READ_LOGS = 'READ_LOGS';
    public const string EDIT_CONTENT = 'EDIT_CONTENT';
    public const string EDIT_TAGS = 'EDIT_TAGS';
    public const string EDIT_REALMS = 'EDIT_REALMS';
    public const string EDIT_SETTING = 'EDIT_SETTING';
    public const string EDIT_STATUS = 'EDIT_STATUS';
    public const string EDIT_ATTRIBUTE = 'EDIT_ATTRIBUTE';
    public const string DELETE = 'DELETE';

    public function __construct(
        private readonly NodeChrootResolver $chrootResolver,
        private readonly Security $security,
        private readonly NodeOffspringResolverInterface $nodeOffspringResolver,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (
            \in_array($attribute, [
                self::CREATE_AT_ROOT,
                self::READ_AT_ROOT,
                self::SEARCH,
                self::EMPTY_TRASH,
            ])
        ) {
            return true;
        }

        if (
            !\in_array($attribute, [
                self::CREATE,
                self::DUPLICATE,
                self::READ,
                self::READ_LOGS,
                self::EDIT_CONTENT,
                self::EDIT_SETTING,
                self::EDIT_TAGS,
                self::EDIT_REALMS,
                self::EDIT_STATUS,
                self::EDIT_ATTRIBUTE,
                self::DELETE,
            ])
        ) {
            return false;
        }

        if ($subject instanceof NodeInterface || $subject instanceof NodesSources) {
            return true;
        }

        return false;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($subject instanceof NodesSources) {
            $subject = $subject->getNode();
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($subject, $user, $vote),
            self::DUPLICATE => $this->canDuplicate($subject, $user, $vote),
            self::CREATE_AT_ROOT => $this->canCreateAtRoot($user, $vote),
            self::READ => $this->canRead($subject, $user, $vote),
            self::READ_AT_ROOT => $this->canReadAtRoot($user, $vote),
            self::SEARCH => $this->canSearch($user, $vote),
            self::READ_LOGS => $this->canReadLogs($subject, $user, $vote),
            self::EDIT_CONTENT => $this->canEditContent($subject, $user, $vote),
            self::EDIT_SETTING => $this->canEditSetting($subject, $user, $vote),
            self::EDIT_STATUS => $this->canEditStatus($subject, $user, $vote),
            self::EDIT_TAGS => $this->canEditTags($subject, $user, $vote),
            self::EDIT_REALMS => $this->canEditRealms($subject, $user, $vote),
            self::EDIT_ATTRIBUTE => $this->canEditAttribute($subject, $user, $vote),
            self::DELETE => $this->canDelete($subject, $user, $vote),
            self::EMPTY_TRASH => $this->canEmptyTrash($user, $vote),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function isNodeInsideUserChroot(NodeInterface $node, NodeInterface $chroot, bool $includeChroot = false, ?Vote $vote = null): bool
    {
        if (!$includeChroot && $chroot->getId() === $node->getId()) {
            $vote?->addReason('Node is the same as user chroot, and chroot itself is not allowed, access denied.');

            return false;
        }

        /*
         * Test if node is inside user chroot using all Chroot node offspring ids
         * to be able to cache all results.
         */
        $insideChroot = \in_array($node->getId(), $this->nodeOffspringResolver->getAllOffspringIds($chroot), true);
        if (!$insideChroot) {
            $vote?->addReason(sprintf(
                'Node %s is not inside user chroot %s.',
                $node->getId(),
                $chroot->getId()
            ));

            return false;
        }

        return true;
    }

    /**
     * @param array<string>|string $roles
     */
    private function isGrantedWithUserChroot(NodeInterface $node, UserInterface $user, array|string $roles, bool $includeChroot, ?Vote $vote = null): bool
    {
        $atLeastOneRoleGranted = is_array($roles)
            ? array_reduce($roles, fn (bool $carry, string $role) => $carry || $this->security->isGranted($role), false)
            : $this->security->isGranted($roles);

        if (!$atLeastOneRoleGranted) {
            $vote?->addReason(sprintf(
                'User has none of required roles: %s.',
                is_array($roles) ? implode(', ', $roles) : $roles
            ));

            return false;
        }

        $chroot = $this->chrootResolver->getChroot($user);

        if (null === $chroot) {
            return true;
        }

        return $this->isNodeInsideUserChroot($node, $chroot, $includeChroot, $vote);
    }

    private function canCreateAtRoot(UserInterface $user, ?Vote $vote = null): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);

        if (null !== $chroot) {
            $vote?->addReason('User has a chroot, cannot create nodes at root.');

            return false;
        }

        if (!$this->security->isGranted('ROLE_ACCESS_NODES')) {
            $vote?->addReason('User does not have ROLE_ACCESS_NODES, cannot create nodes at root.');

            return false;
        }

        return true;
    }

    private function canReadAtRoot(UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->canCreateAtRoot($user, $vote);
    }

    /*
     * All node users can search even if they are chroot-ed
     */
    private function canSearch(UserInterface $user, ?Vote $vote = null): bool
    {
        $vote?->addReason('Checking if user can search nodes with ROLE_ACCESS_NODES.');

        return $this->security->isGranted('ROLE_ACCESS_NODES');
    }

    private function canEmptyTrash(UserInterface $user, ?Vote $vote = null): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);

        if (null !== $chroot) {
            $vote?->addReason('User has a chroot, cannot empty trash.');

            return false;
        }

        if (!$this->security->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            $vote?->addReason('User does not have ROLE_ACCESS_NODES_DELETE, cannot empty trash.');

            return false;
        }

        return true;
    }

    private function canCreate(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        /*
         * Creation is allowed only if node is inside user chroot,
         * user CAN create a chroot child.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', true, $vote);
    }

    private function canRead(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        /*
         * Read is allowed only if node is inside user chroot,
         * user CAN read or list the chroot node children.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', true, $vote);
    }

    private function canReadLogs(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_LOGS'], false, $vote);
    }

    private function canEditContent(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        /*
         * Edition is allowed only if node is inside user chroot,
         * user cannot edit its chroot content.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', false, $vote);
    }

    private function canEditTags(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_TAGS'], false, $vote);
    }

    private function canEditRealms(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_REALM_NODES', false, $vote);
    }

    private function canDuplicate(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        /*
         * Duplication is allowed only if node is inside user chroot,
         * user cannot duplicate its chroot.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', false, $vote);
    }

    private function canEditSetting(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_SETTING', false, $vote);
    }

    private function canEditStatus(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_STATUS', false, $vote);
    }

    private function canDelete(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_DELETE', false, $vote);
    }

    private function canEditAttribute(NodeInterface $node, UserInterface $user, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODE_ATTRIBUTES', false, $vote);
    }
}
