<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Security\Authorization\Voter;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class RealmVoterTest extends TestCase
{
    private function createVoter(
        ?AccessDecisionManagerInterface $accessDecisionManager = null,
        ?RequestStack $requestStack = null,
    ): RealmVoter {
        return new RealmVoter(
            $accessDecisionManager ?? $this->createMock(AccessDecisionManagerInterface::class),
            $requestStack ?? $this->createMock(RequestStack::class),
        );
    }

    private function createRequestStack(?Request $request): RequestStack
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        return $requestStack;
    }

    private function createToken(?UserInterface $user = null): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getUserIdentifier')->willReturn($user?->getUserIdentifier() ?? '');

        return $token;
    }

    private function createUser(string $identifier): UserInterface
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn($identifier);

        return $user;
    }

    /*
     * --- Role realm ---
     */

    public function testRoleRealmGrantsAccessWhenUserHasRole(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_ROLE);
        $realm->method('getRole')->willReturn('ROLE_ADMIN');

        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->method('decide')->with(self::anything(), ['ROLE_ADMIN'])->willReturn(true);

        $voter = $this->createVoter($accessDecisionManager);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testRoleRealmDeniesAccessWhenUserLacksRole(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_ROLE);
        $realm->method('getRole')->willReturn('ROLE_ADMIN');

        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->method('decide')->willReturn(false);

        $voter = $this->createVoter($accessDecisionManager);

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testRoleRealmDeniesAccessWhenRealmHasNoRole(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_ROLE);
        $realm->method('getRole')->willReturn(null);

        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::never())->method('decide');

        $voter = $this->createVoter($accessDecisionManager);

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    /*
     * --- User-list realm ---
     */

    public function testUserRealmGrantsAccessWhenUserInList(): void
    {
        $user = $this->createUser('jane@example.com');

        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_USER);
        $realm->method('getUsers')->willReturn(new ArrayCollection([$user]));

        $voter = $this->createVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($user), $realm, [RealmVoter::READ]),
        );
    }

    public function testUserRealmDeniesAccessWhenUserNotInList(): void
    {
        $listedUser = $this->createUser('jane@example.com');
        $otherUser = $this->createUser('john@example.com');

        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_USER);
        $realm->method('getUsers')->willReturn(new ArrayCollection([$listedUser]));

        $voter = $this->createVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($otherUser), $realm, [RealmVoter::READ]),
        );
    }

    public function testUserRealmDeniesAccessWhenRealmHasNoUsers(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_USER);
        $realm->method('getUsers')->willReturn(new ArrayCollection());

        $voter = $this->createVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($this->createUser('jane@example.com')), $realm, [RealmVoter::READ]),
        );
    }

    /*
     * --- Password realm ---
     */

    public function testPasswordRealmGrantsAccessWithCorrectAuthorizationHeader(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_PLAIN_PASSWORD);
        $realm->method('getPlainPassword')->willReturn('s3cret');

        $request = Request::create('/');
        $request->headers->set('Authorization', 'PasswordQuery s3cret');

        $voter = $this->createVoter(requestStack: $this->createRequestStack($request));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testPasswordRealmGrantsAccessWithCorrectQueryParameter(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_PLAIN_PASSWORD);
        $realm->method('getPlainPassword')->willReturn('s3cret');

        $request = Request::create('/?password=s3cret');

        $voter = $this->createVoter(requestStack: $this->createRequestStack($request));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testPasswordRealmDeniesAccessWithWrongPassword(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_PLAIN_PASSWORD);
        $realm->method('getPlainPassword')->willReturn('s3cret');

        $request = Request::create('/?password=wrong');

        $voter = $this->createVoter(requestStack: $this->createRequestStack($request));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testPasswordRealmDeniesAccessWhenPasswordMissing(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_PLAIN_PASSWORD);
        $realm->method('getPlainPassword')->willReturn('s3cret');

        $request = Request::create('/');

        $voter = $this->createVoter(requestStack: $this->createRequestStack($request));

        // No password submitted at all: voteForPassword() returns false explicitly (DENIED), not ABSTAIN.
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    public function testPasswordRealmDeniesAccessWhenRealmHasNoStoredPassword(): void
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getType')->willReturn(RealmInterface::TYPE_PLAIN_PASSWORD);
        $realm->method('getPlainPassword')->willReturn(null);

        $request = Request::create('/?password=s3cret');

        $voter = $this->createVoter(requestStack: $this->createRequestStack($request));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $realm, [RealmVoter::READ]),
        );
    }

    /*
     * --- verifyPassword() (private method, invoked via reflection) ---
     */

    public function testVerifyPasswordAcceptsMatchingBcryptHash(): void
    {
        $hash = password_hash('s3cret', PASSWORD_BCRYPT);

        self::assertTrue($this->callVerifyPassword('s3cret', $hash));
    }

    public function testVerifyPasswordAcceptsMatchingLegacyPlaintext(): void
    {
        self::assertTrue($this->callVerifyPassword('s3cret', 's3cret'));
    }

    public function testVerifyPasswordRejectsMismatch(): void
    {
        self::assertFalse($this->callVerifyPassword('wrong', 's3cret'));
    }

    private function callVerifyPassword(string $submittedPassword, string $storedPassword): bool
    {
        $method = new \ReflectionMethod(RealmVoter::class, 'verifyPassword');

        return $method->invoke($this->createVoter(), $submittedPassword, $storedPassword);
    }

    /*
     * --- extractPassword() (private method, invoked via reflection) ---
     */

    public function testExtractPasswordReadsAuthorizationHeader(): void
    {
        $request = Request::create('/');
        $request->headers->set('Authorization', 'PasswordQuery s3cret');

        self::assertSame('s3cret', $this->callExtractPassword($request));
    }

    public function testExtractPasswordReadsQueryParameter(): void
    {
        $request = Request::create('/?password=s3cret');

        self::assertSame('s3cret', $this->callExtractPassword($request));
    }

    public function testExtractPasswordReturnsNullOnWrongAuthorizationScheme(): void
    {
        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer s3cret');

        self::assertNull($this->callExtractPassword($request));
    }

    public function testExtractPasswordReturnsNullWhenAbsent(): void
    {
        $request = Request::create('/');

        self::assertNull($this->callExtractPassword($request));
    }

    private function callExtractPassword(Request $request): ?string
    {
        $method = new \ReflectionMethod(RealmVoter::class, 'extractPassword');

        return $method->invoke($this->createVoter(), $request);
    }
}
