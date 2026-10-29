<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Tests\State;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;

/**
 * Functional coverage of the email-validation token consumption endpoint
 * (PUT /api/users/validate), handled by UserValidationTokenProcessor:
 * ROLE_USER requirement, token-belongs-to-current-user matching (no
 * cross-account validation), single-use invalidation and expiry.
 *
 * The /api firewall is stateless and reloads its user straight from the JWT
 * payload (security.yaml: `provider: jwt`), so the session-based
 * loginUser() helper has no effect on it. Requests are authenticated here
 * with a real JWT minted via JWTTokenManagerInterface::create(), the same
 * mechanism the app's own login flow uses.
 *
 * Hits the real HTTP endpoint against the test database (no repository
 * mocking) since the single-use guarantee lives in the persistence + HTTP
 * contract, not in isolated unit logic.
 */
final class UserValidationTokenProcessorTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const string ENDPOINT = '/api/users/validate';
    private const string EMAIL_VALIDATED_ROLE = 'ROLE_EMAIL_VALIDATED';

    public function testValidTokenGrantsRoleAndInvalidatesToken(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();
        $token = 'valid-'.uniqid();
        $this->createValidationToken($em, $user, $token, new \DateTime('+1 hour'));

        $client = self::createClient();
        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token],
            'headers' => ['Authorization' => 'Bearer '.$this->jwtFor($user)],
        ]);

        self::assertResponseIsSuccessful();

        $fresh = $this->reloadUser($userId);
        self::assertContains(self::EMAIL_VALIDATED_ROLE, $fresh->getUserRoles());
        self::assertNull($this->findToken($token), 'Token is removed after being consumed (single-use).');
    }

    public function testTokenBelongingToDifferentUserIsRejected(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $userA = $this->createUser($em);
        $userAId = $userA->getId();
        $userB = $this->createUser($em);
        $userBId = $userB->getId();
        $token = 'other-user-'.uniqid();
        $this->createValidationToken($em, $userB, $token, new \DateTime('+1 hour'));

        // Logged in as A, submitting B's valid token.
        $client = self::createClient();
        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token],
            'headers' => ['Authorization' => 'Bearer '.$this->jwtFor($userA)],
        ]);

        self::assertResponseStatusCodeSame(403);

        self::assertNotContains(self::EMAIL_VALIDATED_ROLE, $this->reloadUser($userAId)->getUserRoles());
        self::assertNotContains(self::EMAIL_VALIDATED_ROLE, $this->reloadUser($userBId)->getUserRoles());
        self::assertNotNull($this->findToken($token), 'Token is untouched: request was rejected before any consumption.');
    }

    public function testReusedTokenIsRejected(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $token = 'reuse-'.uniqid();
        $this->createValidationToken($em, $user, $token, new \DateTime('+1 hour'));

        $client = self::createClient();
        $jwt = $this->jwtFor($user);

        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token],
            'headers' => ['Authorization' => 'Bearer '.$jwt],
        ]);
        self::assertResponseIsSuccessful();

        // Second attempt reuses the very same, now-consumed token.
        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token],
            'headers' => ['Authorization' => 'Bearer '.$jwt],
        ]);

        // UserValidationTokenRepository::findOneByValidToken() no longer
        // finds a row at all (it was removed), so this collapses onto the
        // very same "token does not exist" 422 path as an unknown token.
        self::assertResponseStatusCodeSame(422);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();
        $token = 'expired-'.uniqid();
        $this->createValidationToken($em, $user, $token, new \DateTime('-1 hour'));

        $client = self::createClient();
        $client->request('PUT', self::ENDPOINT, [
            'json' => ['token' => $token],
            'headers' => ['Authorization' => 'Bearer '.$this->jwtFor($user)],
        ]);

        // UserValidationTokenRepository::findOneByValidToken() filters out
        // expired rows at the query level, so this also surfaces as
        // "token does not exist or is not valid anymore" (422), same as an
        // unknown/reused token.
        self::assertResponseStatusCodeSame(422);

        self::assertNotContains(self::EMAIL_VALIDATED_ROLE, $this->reloadUser($userId)->getUserRoles());
        self::assertNotNull($this->findToken($token), 'Expired token row is left untouched by this endpoint (purged separately).');
    }

    private function jwtFor(User $user): string
    {
        return self::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('validate_'.$suffix);
        $user->setEmail('validate_'.$suffix.'@example.test');
        $user->setPlainPassword('SomePassword123!');
        $user->setEnabled(true);
        // ROLE_USER is required by both the /api/users access_control rule
        // and the processor's own isGranted('ROLE_USER') check.
        $user->setUserRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createValidationToken(
        EntityManagerInterface $em,
        User $user,
        string $token,
        \DateTime $validUntil,
    ): UserValidationToken {
        $validationToken = new UserValidationToken();
        $validationToken->setUser($user);
        $validationToken->setToken($token);
        $validationToken->setTokenValidUntil($validUntil);

        $em->persist($validationToken);
        $em->flush();

        return $validationToken;
    }

    /**
     * Reloads the user straight from the database, bypassing any identity
     * map left over from before the HTTP request (the kernel may have
     * rebooted with a fresh EntityManager in between).
     */
    private function reloadUser(?int $userId): User
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $user = $em->getRepository(User::class)->find($userId);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function findToken(string $token): ?UserValidationToken
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        return $em->getRepository(UserValidationToken::class)->findOneBy(['token' => $token]);
    }
}
