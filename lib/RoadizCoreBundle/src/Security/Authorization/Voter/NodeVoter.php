<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Make access decisions on each node/node-source action.
 *
 * This is overridable from project by inheriting this class and overriding service.
 *
 * @extends Voter<'CREATE'|'DUPLICATE'|'CREATE_AT_ROOT'|'SEARCH'|'READ'|'READ_AT_ROOT'|'EMPTY_TRASH'|'READ_LOGS'|'EDIT_CONTENT'|'EDIT_TAGS'|'EDIT_REALMS'|'EDIT_SETTING'|'EDIT_STATUS'|'EDIT_ATTRIBUTE'|'DELETE', Node>
 */
class NodeVoter extends Voter
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
        private readonly NodeOffspringResolverInterface $nodeOffspringResolver,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
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

        if (is_a($subject, NodeInterface::class, true)
            || is_a($subject, NodesSources::class, true)) {
            return true;
        }

        return false;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($subject instanceof NodesSources) {
            $subject = $subject->getNode();
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($subject, $token, $vote),
            self::DUPLICATE => $this->canDuplicate($subject, $token, $vote),
            self::CREATE_AT_ROOT => $this->canCreateAtRoot($token, $vote),
            self::READ => $this->canRead($subject, $token, $vote),
            self::READ_AT_ROOT => $this->canReadAtRoot($token, $vote),
            self::SEARCH => $this->canSearch($token, $vote),
            self::READ_LOGS => $this->canReadLogs($subject, $token, $vote),
            self::EDIT_CONTENT => $this->canEditContent($subject, $token, $vote),
            self::EDIT_SETTING => $this->canEditSetting($subject, $token, $vote),
            self::EDIT_STATUS => $this->canEditStatus($subject, $token, $vote),
            self::EDIT_TAGS => $this->canEditTags($subject, $token, $vote),
            self::EDIT_REALMS => $this->canEditRealms($subject, $token, $vote),
            self::EDIT_ATTRIBUTE => $this->canEditAttribute($subject, $token, $vote),
            self::DELETE => $this->canDelete($subject, $token, $vote),
            self::EMPTY_TRASH => $this->canEmptyTrash($token, $vote),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function isNodeInsideUserChroot(NodeInterface $node, NodeInterface $chroot, bool $includeChroot = false, ?Vote $vote = null): bool
    {
        if ($chroot->getId() === $node->getId()) {
            $vote?->addReason('Node is the same as user chroot.');
            if (!$includeChroot) {
                $vote?->addReason('Chroot itself is not allowed for this attribute, access denied.');

                return false;
            }
            $vote?->addReason('Chroot itself is allowed for this attribute, access granted.');

            return true;
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

        $vote?->addReason(sprintf(
            'Node %s is inside user chroot %s.',
            $node->getId(),
            $chroot->getId()
        ));

        return true;
    }

    /**
     * @param array<string>|string $roles
     */
    private function isGrantedWithUserChroot(NodeInterface $node, TokenInterface $token, array|string $roles, bool $includeChroot, ?Vote $vote = null): bool
    {
        $atLeastOneRoleGranted = is_array($roles)
            ? array_reduce($roles, fn (bool $carry, string $role) => $carry || $this->accessDecisionManager->decide($token, [$role]), false)
            : $this->accessDecisionManager->decide($token, [$roles]);

        if (!$atLeastOneRoleGranted) {
            $vote?->addReason(sprintf(
                'User has none of required roles: %s.',
                is_array($roles) ? implode(', ', $roles) : $roles
            ));

            return false;
        }

        $chroot = $this->chrootResolver->getChroot($token->getUser());

        if (null === $chroot) {
            return true;
        }

        return $this->isNodeInsideUserChroot($node, $chroot, $includeChroot, $vote);
    }

    protected function canCreateAtRoot(TokenInterface $token, ?Vote $vote = null): bool
    {
        $chroot = $this->chrootResolver->getChroot($token->getUser());

        if (null !== $chroot) {
            $vote?->addReason('User has a chroot, cannot create nodes at root.');

            return false;
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_NODES'])) {
            $vote?->addReason('User does not have ROLE_ACCESS_NODES, cannot create nodes at root.');

            return false;
        }

        return true;
    }

    protected function canReadAtRoot(TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->canCreateAtRoot($token, $vote);
    }

    /*
     * All node users can search even if they are chroot-ed
     */
    protected function canSearch(TokenInterface $token, ?Vote $vote = null): bool
    {
        $vote?->addReason('Checking if user can search nodes with ROLE_ACCESS_NODES.');

        return $this->accessDecisionManager->decide($token, ['ROLE_ACCESS_NODES']);
    }

    protected function canEmptyTrash(TokenInterface $token, ?Vote $vote = null): bool
    {
        $chroot = $this->chrootResolver->getChroot($token->getUser());

        if (null !== $chroot) {
            $vote?->addReason('User has a chroot, cannot empty trash.');

            return false;
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_NODES_DELETE'])) {
            $vote?->addReason('User does not have ROLE_ACCESS_NODES_DELETE, cannot empty trash.');

            return false;
        }

        return true;
    }

    protected function canCreate(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        /*
         * Creation is allowed only if node is inside user chroot,
         * user CAN create a chroot child.
         */
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES', true, $vote);
    }

    protected function canRead(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        /*
         * Read is allowed only if node is inside user chroot,
         * user CAN read or list the chroot node children.
         */
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES', true, $vote);
    }

    protected function canReadLogs(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_LOGS'], false, $vote);
    }

    protected function canEditContent(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        /*
         * Edition is allowed only if node is inside user chroot,
         * user cannot edit its chroot content.
         */
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES', false, $vote);
    }

    protected function canEditTags(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_TAGS'], false, $vote);
    }

    protected function canEditRealms(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_REALM_NODES', false, $vote);
    }

    protected function canDuplicate(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        /*
         * Duplication is allowed only if node is inside user chroot,
         * user cannot duplicate its chroot.
         */
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES', false, $vote);
    }

    protected function canEditSetting(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES_SETTING', false, $vote);
    }

    protected function canEditStatus(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES_STATUS', false, $vote);
    }

    protected function canDelete(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODES_DELETE', false, $vote);
    }

    protected function canEditAttribute(NodeInterface $node, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->isGrantedWithUserChroot($node, $token, 'ROLE_ACCESS_NODE_ATTRIBUTES', false, $vote);
    }
}
