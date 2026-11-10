<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Tests\State;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Message\UserPasswordResetLinkNotifyMessage;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * Signing up with an email that's already registered must not reveal that
 * fact (no 422 distinguishing it from a fresh signup) — the existing
 * account holder is notified out-of-band instead.
 */
final class UserSignupProcessorTest extends ApiTestCase
{
    private const string ENDPOINT = '/api/users/signup';
    private const string CAPTCHA_HEADER = 'x-test-captcha';

    public function testSignupWithExistingEmailReturnsSuccessAndNotifiesExistingAccount(): void
    {
        $client = self::createClient();
        $this->overrideCaptcha();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();
        $usersCountBefore = (int) $em->getRepository(User::class)->count([]);

        $response = $client->request('POST', self::ENDPOINT, [
            'json' => [
                'email' => $user->getEmail(),
                'plainPassword' => 'Some-Very-Strong-Password-42!',
            ],
            'headers' => [
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $em->clear();
        self::assertSame($usersCountBefore, $em->getRepository(User::class)->count([]));

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->getSent();
        self::assertCount(1, $envelopes);
        $message = $envelopes[0]->getMessage();
        self::assertInstanceOf(UserPasswordResetLinkNotifyMessage::class, $message);
        self::assertSame($userId, $message->getUserId());
    }

    private function overrideCaptcha(): void
    {
        self::getContainer()->set(CaptchaServiceInterface::class, new class implements CaptchaServiceInterface {
            public function getFieldName(): string
            {
                return 'test-captcha';
            }

            public function isEnabled(): bool
            {
                return true;
            }

            public function getPublicKey(): ?string
            {
                return null;
            }

            public function getFormWidgetName(): string
            {
                return 'test-captcha';
            }

            public function check(string $responseValue): true|string|array
            {
                return true;
            }
        });
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('signup_'.$suffix);
        $user->setEmail('signup_'.$suffix.'@example.test');
        $user->setPlainPassword('OldPassword123!');
        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
