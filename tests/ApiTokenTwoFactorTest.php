<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;

/**
 * POST /api/token must not mint a full-access JWT for a 2FA-enabled account
 * on username+password alone, since Scheb 2FA (session-based) cannot run on
 * the stateless api_login firewall.
 */
final class ApiTokenTwoFactorTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const string ENDPOINT = '/api/token';
    private const string PASSWORD = 'Some-Very-Strong-Password-42!';

    public function testTwoFactorEnabledAccountWithoutCodeIsRejected(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $this->enableTwoFactor($em, $user);

        $response = self::createClient()->request('POST', self::ENDPOINT, [
            'json' => ['username' => $user->getUsername(), 'password' => self::PASSWORD],
        ]);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testTwoFactorEnabledAccountWithValidCodeIsAccepted(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $secret = TOTP::create()->getSecret();
        $this->enableTwoFactor($em, $user, $secret);
        $code = TOTP::create($secret)->now();

        $response = self::createClient()->request('POST', self::ENDPOINT, [
            'json' => ['username' => $user->getUsername(), 'password' => self::PASSWORD, '_auth_code' => $code],
        ]);

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('token', $response->toArray());
    }

    public function testAccountWithoutTwoFactorIsUnaffected(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);

        $response = self::createClient()->request('POST', self::ENDPOINT, [
            'json' => ['username' => $user->getUsername(), 'password' => self::PASSWORD],
        ]);

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('token', $response->toArray());
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setUsername('2fa_api_'.uniqid());
        $user->setEmail(uniqid().'@example.test');
        $user->setPlainPassword(self::PASSWORD);
        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function enableTwoFactor(EntityManagerInterface $em, User $user, ?string $secret = null): TwoFactorUser
    {
        $twoFactorUser = new TwoFactorUser();
        $twoFactorUser->setUser($user);
        $twoFactorUser->setSecret($secret ?? TOTP::create()->getSecret());
        $twoFactorUser->setActivatedAt(new \DateTime());

        $em->persist($twoFactorUser);
        $em->flush();

        return $twoFactorUser;
    }
}
