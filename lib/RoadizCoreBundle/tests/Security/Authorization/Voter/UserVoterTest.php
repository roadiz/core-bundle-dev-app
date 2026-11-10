<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Security\Authorization\Voter;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\UserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoterTest extends TestCase
{
    private function createVoter(AccessDecisionManagerInterface $accessDecisionManager): UserVoter
    {
        return new UserVoter($accessDecisionManager);
    }

    /**
     * @param array<string> $grantedRoles roles the AccessDecisionManager grants; anything else is denied
     */
    private function createAccessDecisionManager(array $grantedRoles): AccessDecisionManagerInterface
    {
        $manager = $this->createMock(AccessDecisionManagerInterface::class);
        $manager->method('decide')->willReturnCallback(
            static fn (TokenInterface $token, array $attributes): bool => \in_array($attributes[0], $grantedRoles, true)
        );

        return $manager;
    }

    private function createToken(UserInterface $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(string $identifier, array $roles = []): UserInterface
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn($identifier);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }

    /*
     * --- ROLE_SUPERADMIN bypass ---
     */

    public function testSuperAdminTokenIsGrantedAnyAttributeEvenOnAdminSubject(): void
    {
        $subject = $this->createUser('other', ['ROLE_SUPERADMIN']);
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_SUPERADMIN']));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::EDIT]),
        );
    }

    /*
     * --- Subject being a super admin is protected from non-super-admin tokens ---
     */

    public function testEditDeniedWhenSubjectIsSuperAdminAndTokenIsNot(): void
    {
        $subject = $this->createUser('other', ['ROLE_SUPERADMIN']);
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS']));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [UserVoter::EDIT]),
        );
    }

    /*
     * --- EDIT: ROLE_ACCESS_USERS or object == user ---
     */

    public function testEditDeniedOnAnotherUserWithoutRole(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager([]));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [UserVoter::EDIT]),
        );
    }

    public function testEditGrantedOnAnotherUserWithRole(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS']));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::EDIT]),
        );
    }

    public function testEditGrantedOnOwnAccountWithoutRole(): void
    {
        $subject = $this->createUser('me');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager([]));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::EDIT]),
        );
    }

    /*
     * --- VIEW_HISTORY ---
     */

    public function testViewHistoryGrantedForOwnAccountRegardlessOfRoles(): void
    {
        $subject = $this->createUser('me');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager([]));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::VIEW_HISTORY]),
        );
    }

    public function testViewHistoryDeniedForOtherUserWithoutLogsRole(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS']));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [UserVoter::VIEW_HISTORY]),
        );
    }

    public function testViewHistoryGrantedForOtherUserWithBothRoles(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_LOGS', 'ROLE_ACCESS_USERS']));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::VIEW_HISTORY]),
        );
    }

    /*
     * --- EDIT_DETAIL: ROLE_ACCESS_USERS_DETAIL is mandatory, then ROLE_ACCESS_USERS or own account ---
     */

    public function testEditDetailDeniedWithoutDetailRoleEvenForOwnAccount(): void
    {
        $subject = $this->createUser('me');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager([]));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [UserVoter::EDIT_DETAIL]),
        );
    }

    public function testEditDetailGrantedForOwnAccountWithDetailRole(): void
    {
        $subject = $this->createUser('me');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS_DETAIL']));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::EDIT_DETAIL]),
        );
    }

    public function testEditDetailDeniedForOtherUserWithDetailRoleButNoUsersRole(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS_DETAIL']));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [UserVoter::EDIT_DETAIL]),
        );
    }

    public function testEditDetailGrantedForOtherUserWithBothRoles(): void
    {
        $subject = $this->createUser('other');
        $token = $this->createToken($this->createUser('me'));
        $voter = $this->createVoter($this->createAccessDecisionManager(['ROLE_ACCESS_USERS_DETAIL', 'ROLE_ACCESS_USERS']));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [UserVoter::EDIT_DETAIL]),
        );
    }
}
