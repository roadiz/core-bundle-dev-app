<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Tests\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManager;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Unit coverage of UserValidationTokenManager:
 * - createForUser()'s "reuse the existing UserValidationToken row vs
 *   persist a new one" branch, and the expiry stamp set on every
 *   (re)generated token;
 * - isUserEmailValidated()'s role-hierarchy check.
 *
 * Plain TestCase, not KernelTestCase: RoleHierarchy is a trivial value
 * object constructed directly (no container/DB needed), and every other
 * collaborator is a bare stub since createForUser() is exercised with
 * sendEmail: false throughout - email dispatch is out of scope for this
 * test and already untouched by the branches under test.
 *
 * Discrepancy vs. a naive reading of createForUser(): the "reuse vs
 * recreate" branch only decides whether a NEW UserValidationToken entity is
 * persisted (based on whether the repository finds *any* existing row for
 * the user, expired or not - see UserValidationTokenRepository::
 * findOneByUser(), which does not filter by validity). It does NOT mean an
 * unexpired token's value is left alone: the token string and expiry are
 * unconditionally regenerated on every call, whether the entity is reused
 * or freshly created.
 */
final class UserValidationTokenManagerTest extends TestCase
{
    private const string EMAIL_VALIDATED_ROLE = 'ROLE_EMAIL_VALIDATED';

    public function testCreateForUserReusesEntityAndRegeneratesTokenWhenAnUnexpiredTokenExists(): void
    {
        $existing = new UserValidationToken();
        $existing->setToken('old-token');
        $existing->setTokenValidUntil(new \DateTime('+10 seconds'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::never())->method('persist');
        $manager = $this->createManager($this->repositoryReturning($existing), $objectManager);

        $result = $manager->createForUser(new InMemoryUser('user-a', null), sendEmail: false);

        self::assertSame($existing, $result, 'Same entity instance is reused, not replaced.');
        self::assertNotSame('old-token', $result->getToken(), 'Token value is regenerated even though the previous one was still valid.');
        self::assertNotEmpty($result->getToken());
    }

    public function testCreateForUserAlsoReusesAnExpiredEntityInsteadOfCreatingANewOne(): void
    {
        $expired = new UserValidationToken();
        $expired->setToken('expired-token');
        $expired->setTokenValidUntil(new \DateTime('-1 hour'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::never())->method('persist');
        $manager = $this->createManager($this->repositoryReturning($expired), $objectManager);

        $result = $manager->createForUser(new InMemoryUser('user-b', null), sendEmail: false);

        // Real behavior: findOneByUser() ignores validity entirely, so an
        // expired row is reused exactly like a valid one - no new entity.
        self::assertSame($expired, $result);
        self::assertNotSame('expired-token', $result->getToken());
    }

    public function testCreateForUserCreatesNewEntityWhenNoneExists(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::once())->method('persist')->with(self::isInstanceOf(UserValidationToken::class));
        $manager = $this->createManager($this->repositoryReturning(null), $objectManager);

        $user = new InMemoryUser('user-c', null);
        $result = $manager->createForUser($user, sendEmail: false);

        self::assertSame($user, $result->getUser());
        self::assertNotEmpty($result->getToken());
    }

    public function testExpiryStampIsSetRelativeToConfiguredDuration(): void
    {
        $manager = $this->createManager($this->repositoryReturning(null), $this->createMock(ObjectManager::class), userValidationExpiresIn: 1800);

        $before = new \DateTime();
        $result = $manager->createForUser(new InMemoryUser('user-d', null), sendEmail: false);
        $after = new \DateTime();

        $validUntil = $result->getTokenValidUntil();
        self::assertNotNull($validUntil);
        self::assertGreaterThanOrEqual(
            (clone $before)->modify('+1800 seconds')->getTimestamp(),
            $validUntil->getTimestamp(),
        );
        self::assertLessThanOrEqual(
            (clone $after)->modify('+1800 seconds')->getTimestamp(),
            $validUntil->getTimestamp(),
        );
    }

    public function testIsUserEmailValidatedTrueWithDirectRole(): void
    {
        $manager = $this->createManager($this->repositoryReturning(null), $this->createMock(ObjectManager::class));
        $user = new InMemoryUser('user-e', null, [self::EMAIL_VALIDATED_ROLE]);

        self::assertTrue($manager->isUserEmailValidated($user));
    }

    public function testIsUserEmailValidatedTrueViaRoleHierarchy(): void
    {
        // ROLE_PUBLIC_USER reaches ROLE_EMAIL_VALIDATED... in reverse in the
        // real app config; here we only need *some* role that resolves to
        // the configured emailValidatedRoleName through the hierarchy.
        $hierarchy = ['ROLE_SOME_GROUP' => [self::EMAIL_VALIDATED_ROLE]];
        $manager = $this->createManager($this->repositoryReturning(null), $this->createMock(ObjectManager::class), hierarchy: $hierarchy);
        $user = new InMemoryUser('user-f', null, ['ROLE_SOME_GROUP']);

        self::assertTrue($manager->isUserEmailValidated($user));
    }

    public function testIsUserEmailValidatedTrueForSuperAdmin(): void
    {
        $manager = $this->createManager($this->repositoryReturning(null), $this->createMock(ObjectManager::class));
        $user = new InMemoryUser('user-g', null, ['ROLE_SUPERADMIN']);

        self::assertTrue($manager->isUserEmailValidated($user));
    }

    public function testIsUserEmailValidatedFalseWithoutRole(): void
    {
        $manager = $this->createManager($this->repositoryReturning(null), $this->createMock(ObjectManager::class));
        $user = new InMemoryUser('user-h', null, ['ROLE_USER']);

        self::assertFalse($manager->isUserEmailValidated($user));
    }

    /**
     * @param array<string, list<string>> $hierarchy
     */
    private function createManager(
        object $repository,
        ObjectManager $objectManager,
        int $userValidationExpiresIn = 3600,
        array $hierarchy = [],
    ): UserValidationTokenManager {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getRepository')->willReturn($repository);
        $registry->method('getManager')->willReturn($objectManager);

        return new UserValidationTokenManager(
            $registry,
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(NotifierInterface::class),
            new RoleHierarchy($hierarchy),
            self::EMAIL_VALIDATED_ROLE,
            $userValidationExpiresIn,
            'https://example.test/validate',
        );
    }

    /**
     * Stubs ManagerRegistry::getRepository()->findOneByUser(), the only
     * repository method createForUser() calls.
     */
    private function repositoryReturning(?UserValidationToken $token): object
    {
        return new class($token) {
            public function __construct(private readonly ?UserValidationToken $token)
            {
            }

            public function findOneByUser(mixed $user): ?UserValidationToken
            {
                return $this->token;
            }
        };
    }
}
