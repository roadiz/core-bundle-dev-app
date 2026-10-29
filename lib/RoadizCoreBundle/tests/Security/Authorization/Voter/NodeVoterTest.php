<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Security\Authorization\Voter;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * NodeVoter delegates all chroot/ancestor-walk logic to the injected
 * NodeChrootResolver + NodeOffspringResolverInterface services (it never
 * walks getParent() itself), so those are mocked directly to simulate
 * "node is/isn't inside user chroot" instead of building a real parent chain.
 */
final class NodeVoterTest extends TestCase
{
    private function createVoter(
        ?NodeChrootResolver $chrootResolver = null,
        ?NodeOffspringResolverInterface $offspringResolver = null,
        ?AccessDecisionManagerInterface $accessDecisionManager = null,
    ): NodeVoter {
        return new NodeVoter(
            $chrootResolver ?? $this->createChrootResolver(null),
            $offspringResolver ?? $this->createMock(NodeOffspringResolverInterface::class),
            $accessDecisionManager ?? $this->createMock(AccessDecisionManagerInterface::class),
        );
    }

    private function createChrootResolver(?Node $chroot): NodeChrootResolver
    {
        $resolver = $this->createMock(NodeChrootResolver::class);
        $resolver->method('getChroot')->willReturn($chroot);

        return $resolver;
    }

    /**
     * @param array<int> $offspringIds
     */
    private function createOffspringResolver(array $offspringIds): NodeOffspringResolverInterface
    {
        $resolver = $this->createMock(NodeOffspringResolverInterface::class);
        $resolver->method('getAllOffspringIds')->willReturn($offspringIds);

        return $resolver;
    }

    private function createAccessDecisionManager(bool $granted): AccessDecisionManagerInterface
    {
        $manager = $this->createMock(AccessDecisionManagerInterface::class);
        $manager->method('decide')->willReturn($granted);

        return $manager;
    }

    private function createToken(): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        return $token;
    }

    private function createNode(int $id): Node
    {
        $node = $this->createMock(Node::class);
        $node->method('getId')->willReturn($id);

        return $node;
    }

    private function createNodesSources(Node $node): NodesSources
    {
        $nodesSources = $this->createMock(NodesSources::class);
        $nodesSources->method('getNode')->willReturn($node);

        return $nodesSources;
    }

    /*
     * --- CREATE ---
     */

    public function testCreateGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::CREATE]),
        );
    }

    public function testCreateDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::CREATE]),
        );
    }

    /*
     * --- DUPLICATE ---
     */

    public function testDuplicateGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::DUPLICATE]),
        );
    }

    public function testDuplicateDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::DUPLICATE]),
        );
    }

    /*
     * --- CREATE_AT_ROOT ---
     */

    public function testCreateAtRootGrantedWhenRoleGrantedAndNoChroot(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), null, [NodeVoter::CREATE_AT_ROOT]),
        );
    }

    public function testCreateAtRootDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), null, [NodeVoter::CREATE_AT_ROOT]),
        );
    }

    /*
     * --- SEARCH (chroot-agnostic: all node users can search even if chroot-ed) ---
     */

    public function testSearchGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), null, [NodeVoter::SEARCH]),
        );
    }

    public function testSearchDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), null, [NodeVoter::SEARCH]),
        );
    }

    /*
     * --- READ ---
     */

    public function testReadGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::READ]),
        );
    }

    public function testReadDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::READ]),
        );
    }

    public function testReadGrantedWithNodesSourcesSubjectUnwrapsToNode(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));
        $nodesSources = $this->createNodesSources($this->createNode(1));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $nodesSources, [NodeVoter::READ]),
        );
    }

    /*
     * --- READ_AT_ROOT (aliases canCreateAtRoot) ---
     */

    public function testReadAtRootGrantedWhenRoleGrantedAndNoChroot(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), null, [NodeVoter::READ_AT_ROOT]),
        );
    }

    public function testReadAtRootDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), null, [NodeVoter::READ_AT_ROOT]),
        );
    }

    /*
     * --- READ_LOGS (at least one of ROLE_ACCESS_NODES / ROLE_ACCESS_LOGS) ---
     */

    public function testReadLogsGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::READ_LOGS]),
        );
    }

    public function testReadLogsDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::READ_LOGS]),
        );
    }

    /*
     * --- EDIT_CONTENT ---
     */

    public function testEditContentGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_CONTENT]),
        );
    }

    public function testEditContentDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_CONTENT]),
        );
    }

    /*
     * --- EDIT_TAGS (at least one of ROLE_ACCESS_NODES / ROLE_ACCESS_TAGS) ---
     */

    public function testEditTagsGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_TAGS]),
        );
    }

    public function testEditTagsDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_TAGS]),
        );
    }

    /*
     * --- EDIT_REALMS ---
     */

    public function testEditRealmsGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_REALMS]),
        );
    }

    public function testEditRealmsDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_REALMS]),
        );
    }

    /*
     * --- EDIT_SETTING ---
     */

    public function testEditSettingGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_SETTING]),
        );
    }

    public function testEditSettingDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_SETTING]),
        );
    }

    /*
     * --- EDIT_STATUS ---
     */

    public function testEditStatusGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_STATUS]),
        );
    }

    public function testEditStatusDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_STATUS]),
        );
    }

    /*
     * --- EDIT_ATTRIBUTE ---
     */

    public function testEditAttributeGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_ATTRIBUTE]),
        );
    }

    public function testEditAttributeDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::EDIT_ATTRIBUTE]),
        );
    }

    /*
     * --- DELETE ---
     */

    public function testDeleteGrantedWhenRoleGranted(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::DELETE]),
        );
    }

    public function testDeleteDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(1), [NodeVoter::DELETE]),
        );
    }

    /*
     * --- EMPTY_TRASH ---
     */

    public function testEmptyTrashGrantedWhenRoleGrantedAndNoChroot(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), null, [NodeVoter::EMPTY_TRASH]),
        );
    }

    public function testEmptyTrashDeniedWhenRoleMissing(): void
    {
        $voter = $this->createVoter(accessDecisionManager: $this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), null, [NodeVoter::EMPTY_TRASH]),
        );
    }

    /*
     * --- Chroot inheritance: a user chroot-ed to node A can act on a
     * descendant of A, but not on a sibling subtree outside A's descendants.
     */

    public function testChrootedUserGrantedOnDescendantNode(): void
    {
        $chroot = $this->createNode(10);
        $descendant = $this->createNode(20);

        $voter = $this->createVoter(
            chrootResolver: $this->createChrootResolver($chroot),
            offspringResolver: $this->createOffspringResolver([11, 20, 21]),
            accessDecisionManager: $this->createAccessDecisionManager(true),
        );

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $descendant, [NodeVoter::EDIT_CONTENT]),
        );
    }

    public function testChrootedUserDeniedOnSiblingSubtreeOutsideChroot(): void
    {
        $chroot = $this->createNode(10);
        $sibling = $this->createNode(99);

        $voter = $this->createVoter(
            chrootResolver: $this->createChrootResolver($chroot),
            offspringResolver: $this->createOffspringResolver([11, 20, 21]),
            accessDecisionManager: $this->createAccessDecisionManager(true),
        );

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $sibling, [NodeVoter::EDIT_CONTENT]),
        );
    }

    public function testChrootedUserDeniedOnChrootNodeItselfWhenActionExcludesChroot(): void
    {
        $chroot = $this->createNode(10);

        $voter = $this->createVoter(
            chrootResolver: $this->createChrootResolver($chroot),
            offspringResolver: $this->createOffspringResolver([]),
            accessDecisionManager: $this->createAccessDecisionManager(true),
        );

        // EDIT_CONTENT does not include the chroot node itself.
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createNode(10), [NodeVoter::EDIT_CONTENT]),
        );
    }

    public function testChrootedUserGrantedOnChrootNodeItselfWhenActionIncludesChroot(): void
    {
        $chroot = $this->createNode(10);

        $voter = $this->createVoter(
            chrootResolver: $this->createChrootResolver($chroot),
            offspringResolver: $this->createOffspringResolver([]),
            accessDecisionManager: $this->createAccessDecisionManager(true),
        );

        // READ includes the chroot node itself.
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createNode(10), [NodeVoter::READ]),
        );
    }
}
