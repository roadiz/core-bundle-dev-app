<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Message\UserPasswordResetLinkNotifyMessage;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * Functional coverage for a timing-oracle gap discovered while checking whether
 * M7's per-email rate-limit fix on RoadizUserBundle's password-reset-request
 * flow also applied to RoadizCoreBundle's own admin flow (it didn't, see M11).
 * The same question surfaced a second, independent L3-class bug in the same
 * admin flow: LoginRequestTrait::sendConfirmationEmail() called
 * UserViewer::sendPasswordResetLink(), which sends synchronously via
 * NotifierInterface::send() — an unknown email returns instantly while a
 * known one blocks on a real SMTP transaction first, letting an attacker time
 * POST /rz-admin/login/request to enumerate admin accounts. Fixed the same
 * way L3 fixed it for password_request: dispatch through Messenger instead
 * (sendPasswordResetLinkAsync()), so this test asserts the dispatch mechanism
 * rather than wall-clock timing (which would be flaky).
 */
final class LoginRequestAsyncEmailTest extends ApiTestCase
{
    use MailerAssertionsTrait;

    public function testExistingEmailDispatchesResetLinkAsynchronously(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $this->createUser($em);
        $userId = $user->getId();

        $token = $this->extractFormToken($client, '/rz-admin/login/request', 'login_request');

        $client->request('POST', '/rz-admin/login/request', [
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
            'extra' => ['parameters' => ['login_request' => [
                'email' => $user->getEmail(),
                '_token' => $token,
            ]]],
        ]);
        self::assertResponseRedirects();

        $fresh = $this->reload($userId);
        self::assertNotNull($fresh->getConfirmationToken());
        self::assertNotNull($fresh->getPasswordRequestedAt());

        // No synchronous mail-send within the request: this is what closes
        // the timing oracle, since the unknown-email branch also returns
        // without doing any send.
        self::assertEmailCount(0);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->getSent();
        self::assertCount(1, $envelopes);
        $message = $envelopes[0]->getMessage();
        self::assertInstanceOf(UserPasswordResetLinkNotifyMessage::class, $message);
        self::assertSame($userId, $message->getUserId());
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('loginrequest_'.$suffix);
        $user->setEmail('loginrequest_'.$suffix.'@example.test');
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

    private function extractFormToken(mixed $client, string $url, string $formName): string
    {
        $page = $client->request('GET', $url);
        self::assertSame(200, $page->getStatusCode());

        preg_match(
            '/name="'.preg_quote($formName, '/').'\[_token\]"[^>]*\svalue="([^"]+)"/',
            $page->getContent(false),
            $matches
        );
        self::assertArrayHasKey(1, $matches, 'CSRF token must be present in the rendered form');

        return $matches[1];
    }

    private function randomIp(): string
    {
        return sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254));
    }
}
