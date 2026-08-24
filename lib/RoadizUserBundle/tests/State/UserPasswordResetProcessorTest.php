<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Tests\State;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Functional coverage of the password reset consumption endpoint
 * (PUT /api/users/password_reset), handled by UserPasswordResetProcessor:
 * token lookup, enabled/locked/expiry checks, password change and
 * single-use token invalidation.
 *
 * Hits the real HTTP endpoint against the test database (no repository
 * mocking) since the single-use guarantee lives in the persistence + HTTP
 * contract, not in isolated unit logic.
 */
final class UserPasswordResetProcessorTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const string ENDPOINT = '/api/users/password_reset';
    private const string OLD_PASSWORD = 'OldPassword123!';
    private const string NEW_PASSWORD = 'NewPassword456!';

    public function testValidTokenChangesPasswordAndInvalidatesToken(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $token = 'valid-'.uniqid();
        $userId = $this->createUserWithResetToken($em, $token, new \DateTime())->getId();

        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token, 'plainPassword' => self::NEW_PASSWORD],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);

        self::assertResponseIsSuccessful();

        $fresh = $this->reload($userId);
        self::assertNull($fresh->getConfirmationToken());
        self::assertNull($fresh->getPasswordRequestedAt());

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($fresh, self::NEW_PASSWORD));
        self::assertFalse($hasher->isPasswordValid($fresh, self::OLD_PASSWORD));
    }

    public function testReusedTokenIsRejectedAndPasswordNotChangedAgain(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $token = 'reuse-'.uniqid();
        $userId = $this->createUserWithResetToken($em, $token, new \DateTime())->getId();
        $ip = $this->randomIp();

        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token, 'plainPassword' => self::NEW_PASSWORD],
            'headers' => ['REMOTE_ADDR' => $ip],
        ]);
        self::assertResponseIsSuccessful();

        // Second attempt reuses the very same, now-consumed token.
        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token, 'plainPassword' => 'AnotherPassword789!'],
            'headers' => ['REMOTE_ADDR' => $ip],
        ]);
        self::assertResponseStatusCodeSame(404);

        $fresh = $this->reload($userId);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($fresh, self::NEW_PASSWORD));
        self::assertFalse($hasher->isPasswordValid($fresh, 'AnotherPassword789!'));
    }

    public function testExpiredTokenIsRejected(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $token = 'expired-'.uniqid();
        // Far beyond any plausible passwordResetExpiresIn configuration.
        $userId = $this->createUserWithResetToken($em, $token, new \DateTime('-30 days'))->getId();

        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token, 'plainPassword' => self::NEW_PASSWORD],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);
        self::assertResponseStatusCodeSame(422);

        $fresh = $this->reload($userId);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($fresh, self::OLD_PASSWORD));
        self::assertSame($token, $fresh->getConfirmationToken());
    }

    public function testDisabledUserTokenIsRejected(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $token = 'disabled-'.uniqid();
        $userId = $this->createUserWithResetToken($em, $token, new \DateTime(), enabled: false)->getId();

        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token, 'plainPassword' => self::NEW_PASSWORD],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);
        self::assertResponseStatusCodeSame(422);

        $fresh = $this->reload($userId);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($fresh, self::OLD_PASSWORD));
        self::assertSame($token, $fresh->getConfirmationToken());
    }

    public function testTooShortNewPasswordFailsValidation(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $token = 'weak-'.uniqid();
        $userId = $this->createUserWithResetToken($em, $token, new \DateTime())->getId();

        $client->request('PUT', self::ENDPOINT, [
            // 8 chars: violates User::$plainPassword's Assert\Length(min: 12).
            'json' => ['token' => $token, 'plainPassword' => 'Ab1!Ab1!'],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);
        self::assertResponseStatusCodeSame(422);

        $fresh = $this->reload($userId);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($fresh, self::OLD_PASSWORD));
        self::assertSame($token, $fresh->getConfirmationToken());
    }

    private function createUserWithResetToken(
        EntityManagerInterface $em,
        string $token,
        \DateTime $passwordRequestedAt,
        bool $enabled = true,
    ): User {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('pwreset_'.$suffix);
        $user->setEmail('pwreset_'.$suffix.'@example.test');
        $user->setPlainPassword(self::OLD_PASSWORD);
        $user->setEnabled($enabled);
        $user->setConfirmationToken($token);
        $user->setPasswordRequestedAt($passwordRequestedAt);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Reloads the user straight from the database, bypassing any identity
     * map left over from before the HTTP request (the kernel may have
     * rebooted with a fresh EntityManager in between).
     */
    private function reload(?int $userId): User
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $user = $em->getRepository(User::class)->find($userId);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function randomIp(): string
    {
        return sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254));
    }
}
