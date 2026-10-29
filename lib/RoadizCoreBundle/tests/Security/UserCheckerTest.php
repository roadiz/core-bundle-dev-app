<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\Exception\UserCredentialsExpiredException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserExpiredException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserLockedException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserNotEnabledException;
use RZ\Roadiz\CoreBundle\Security\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/*
 * Discrepancy vs. the plan: UserChecker does not throw Symfony's built-in
 * DisabledException/LockedException/CredentialsExpiredException/AccountExpiredException.
 * It throws app-specific subclasses of AuthenticationException
 * (UserNotEnabledException, UserLockedException, UserCredentialsExpiredException,
 * UserExpiredException) declared in RZ\Roadiz\CoreBundle\Security\Exception.
 * Tests assert the real exception classes.
 */
final class UserCheckerTest extends TestCase
{
    private function createUserChecker(): UserChecker
    {
        return new UserChecker();
    }

    public function testCheckPreAuthThrowsForDisabledUser(): void
    {
        $user = new User();
        $user->setEnabled(false);

        $this->expectException(UserNotEnabledException::class);

        $this->createUserChecker()->checkPreAuth($user);
    }

    public function testCheckPreAuthPassesForEnabledUser(): void
    {
        $user = new User();
        $user->setEnabled(true);

        $this->createUserChecker()->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthThrowsForLockedUser(): void
    {
        $user = new User();
        $user->setLocked(true);

        $this->expectException(UserLockedException::class);

        $this->createUserChecker()->checkPostAuth($user);
    }

    public function testCheckPostAuthThrowsForExpiredCredentials(): void
    {
        $user = new User();
        $user->setCredentialsExpiresAt(new \DateTime('-1 day'));

        $this->expectException(UserCredentialsExpiredException::class);

        $this->createUserChecker()->checkPostAuth($user);
    }

    public function testCheckPostAuthThrowsForExpiredAccount(): void
    {
        $user = new User();
        $user->setExpiresAt(new \DateTime('-1 day'));

        $this->expectException(UserExpiredException::class);

        $this->createUserChecker()->checkPostAuth($user);
    }

    public function testCheckPostAuthPassesWhenAccountIsInGoodStanding(): void
    {
        $user = new User();
        $user->setLocked(false);
        $user->setCredentialsExpiresAt(null);
        $user->setExpiresAt(null);

        $this->createUserChecker()->checkPostAuth($user);

        $this->addToAssertionCount(1);
    }

    /**
     * UserChecker type-checks `instanceof User` and returns early for any
     * other UserInterface implementation, so non-app principals (e.g. JWT/OpenID
     * users not backed by this entity) are silently ignored on both paths.
     */
    public function testNonAppUserIsIgnoredOnBothPaths(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->expects(self::never())->method(self::anything());

        $checker = $this->createUserChecker();
        $checker->checkPreAuth($user);
        $checker->checkPostAuth($user);

        $this->addToAssertionCount(1);
    }
}
