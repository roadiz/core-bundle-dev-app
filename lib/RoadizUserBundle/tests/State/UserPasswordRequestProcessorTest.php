<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Tests\State;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Message\UserPasswordRequestNotifyMessage;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * Functional coverage of the password reset REQUEST endpoint
 * (POST /api/users/password_request), handled by UserPasswordRequestProcessor:
 * the anti user-enumeration guarantee (identical response for known and
 * unknown identifiers), confirmation token issuance + async notification
 * dispatch, per-IP and per-email rate limiting, and token rollback when
 * message dispatch fails.
 *
 * Hits the real HTTP endpoint against the test database (no repository
 * mocking) since the enumeration guarantee lives in the HTTP contract, not
 * in isolated unit logic.
 */
final class UserPasswordRequestProcessorTest extends ApiTestCase
{
    use MailerAssertionsTrait;

    protected static ?bool $alwaysBootKernel = true;

    private const string ENDPOINT = '/api/users/password_request';

    /*
     * Whichever CaptchaServiceInterface is compiled for this environment
     * (e.g. TurnstileCaptchaService, when local Cloudflare keys are
     * configured) would otherwise make a real network call to validate the
     * header. overrideCaptcha() replaces it with an in-memory stub so these
     * tests stay hermetic; CAPTCHA_HEADER matches the stub's field name.
     */
    private const string CAPTCHA_HEADER = 'x-test-captcha';

    public function testUnknownAndExistingEmailReturnIdenticalResponse(): void
    {
        $client = self::createClient();
        // Keep the same kernel (and our captcha override) across both
        // requests below: the test client reboots the kernel by default
        // before every request, which would otherwise revert the override.
        $client->disableReboot();
        $this->overrideCaptcha();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);

        $existingResponse = $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => $user->getEmail()],
            'headers' => [
                'REMOTE_ADDR' => $this->randomIp(),
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);
        $existingStatus = $existingResponse->getStatusCode();
        $existingShape = $this->normalizeBody($existingResponse->getContent(false));

        $unknownResponse = $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => 'nobody-'.uniqid().'@example.test'],
            'headers' => [
                'REMOTE_ADDR' => $this->randomIp(),
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);

        self::assertSame($existingStatus, $unknownResponse->getStatusCode());
        self::assertSame($existingShape, $this->normalizeBody($unknownResponse->getContent(false)));
    }

    public function testExistingEmailSetsConfirmationTokenAndDispatchesNotificationAsynchronously(): void
    {
        $client = self::createClient();
        $this->overrideCaptcha();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();

        $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => $user->getEmail()],
            'headers' => [
                'REMOTE_ADDR' => $this->randomIp(),
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);
        self::assertResponseIsSuccessful();

        $fresh = $this->reload($userId);
        self::assertNotNull($fresh->getConfirmationToken());
        self::assertNotNull($fresh->getPasswordRequestedAt());

        // No synchronous mail-send within the request: this is what closes
        // the password_request timing side-channel (L3), since the
        // non-existent-user branch also returns without doing any send.
        self::assertEmailCount(0);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->getSent();
        self::assertCount(1, $envelopes);
        $message = $envelopes[0]->getMessage();
        self::assertInstanceOf(UserPasswordRequestNotifyMessage::class, $message);
        self::assertSame($userId, $message->getUserId());
    }

    public function testTooManyRequestsFromSameIpAreThrottled(): void
    {
        // Rate limiter state is Redis-backed with no test-env override, so it
        // survives across full-suite runs. Flush it here to keep this test
        // idempotent across repeated local runs of this fixed IP.
        self::getContainer()->get('cache.password_request_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $this->overrideCaptcha();
        $fixedIp = '203.0.113.55';

        // config/packages/framework.yaml: password_request limiter is a
        // token_bucket, limit 3, refilling 3/minute => burst capacity is 3.
        for ($i = 0; $i < 3; ++$i) {
            $response = $client->request('POST', self::ENDPOINT, [
                'json' => ['identifier' => 'throttle-'.uniqid().'@example.test'],
                'headers' => [
                    'REMOTE_ADDR' => $fixedIp,
                    self::CAPTCHA_HEADER => 'response',
                ],
            ]);
            self::assertLessThan(300, $response->getStatusCode());
        }

        $throttled = $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => 'throttle-'.uniqid().'@example.test'],
            'headers' => [
                'REMOTE_ADDR' => $fixedIp,
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testTooManyRequestsForSameEmailFromDifferentIpsAreThrottled(): void
    {
        // Same idempotency concern as testTooManyRequestsFromSameIpAreThrottled above,
        // but for the per-email limiter's cache pool (prepended by RoadizUserExtension).
        self::getContainer()->get('cache.password_request_email_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $this->overrideCaptcha();
        // A fixed target identifier requested from a different IP each time:
        // the per-IP limiter alone would never trip, but the per-email
        // limiter must still cap requests aimed at one target (M7).
        $targetIdentifier = 'flood-target-'.uniqid().'@example.test';

        // config/packages/framework.yaml: password_request_email limiter is
        // a fixed_window, limit 5 per hour.
        for ($i = 0; $i < 5; ++$i) {
            $response = $client->request('POST', self::ENDPOINT, [
                'json' => ['identifier' => $targetIdentifier],
                'headers' => [
                    'REMOTE_ADDR' => $this->randomIp(),
                    self::CAPTCHA_HEADER => 'response',
                ],
            ]);
            self::assertLessThan(300, $response->getStatusCode());
        }

        $throttled = $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => $targetIdentifier],
            'headers' => [
                'REMOTE_ADDR' => $this->randomIp(),
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testMessageBusFailureRollsBackConfirmationToken(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();

        $client = self::createClient();
        $this->overrideCaptcha();
        // Force message dispatch to fail so the processor's catch block
        // rolls back setConfirmationToken()/setPasswordRequestedAt() to null.
        self::getContainer()->set(MessageBusInterface::class, new class implements MessageBusInterface {
            public function dispatch(object $message, array $stamps = []): Envelope
            {
                throw new TransportException('Simulated message bus failure.');
            }
        });

        $client->request('POST', self::ENDPOINT, [
            'json' => ['identifier' => $user->getEmail()],
            'headers' => [
                'REMOTE_ADDR' => $this->randomIp(),
                self::CAPTCHA_HEADER => 'response',
            ],
        ]);
        // The processor catches the dispatch exception itself, so the
        // request still completes successfully (no half-state is surfaced
        // to the client either).
        self::assertResponseIsSuccessful();

        $fresh = $this->reload($userId);
        self::assertNull($fresh->getConfirmationToken());
        self::assertNull($fresh->getPasswordRequestedAt());
    }

    /**
     * Replaces the compiled CaptchaServiceInterface with an in-memory stub
     * that always accepts, matching CAPTCHA_HEADER, so tests never depend on
     * whichever real captcha provider (and secret keys) happen to be
     * configured for this environment.
     */
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

    /**
     * Decodes a VoidOutput response body and strips its "@id", a JSON-LD
     * blank node identifier (/api/.well-known/genid/...) that API Platform
     * generates randomly per-response and therefore never matches across
     * two distinct requests, even though the rest of the body is identical.
     */
    private function normalizeBody(string $content): array
    {
        $data = json_decode($content, true, flags: \JSON_THROW_ON_ERROR);
        unset($data['@id']);

        return $data;
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('pwrequest_'.$suffix);
        $user->setEmail('pwrequest_'.$suffix.'@example.test');
        $user->setPlainPassword('OldPassword123!');
        $user->setEnabled(true);

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
