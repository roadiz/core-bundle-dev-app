<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

/**
 * Functional coverage for the security-audit follow-up to M7: the admin
 * (RoadizRozierBundle) password-reset-request, password-reset-consumption,
 * and login-link-request flows had no rate limiting at all (M7 only fixed
 * the sibling RoadizUserBundle public-user flow). Asserts the per-IP and
 * per-email limiters added to LoginRequestController, LoginResetController,
 * and SecurityController::requestLoginLink actually trip.
 */
final class RozierLoginRateLimitTest extends ApiTestCase
{
    public function testLoginRequestIsThrottledPerIp(): void
    {
        self::getContainer()->get('cache.login_request_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $fixedIp = '203.0.113.10';

        // login_request limiter: token_bucket, limit 3, refilling 3/minute => burst capacity 3.
        for ($i = 0; $i < 3; ++$i) {
            $response = $client->request('POST', '/rz-admin/login/request', [
                'headers' => ['REMOTE_ADDR' => $fixedIp],
                'extra' => ['parameters' => ['login_request' => ['email' => 'throttle-'.uniqid().'@example.test']]],
            ]);
            self::assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $client->request('POST', '/rz-admin/login/request', [
            'headers' => ['REMOTE_ADDR' => $fixedIp],
            'extra' => ['parameters' => ['login_request' => ['email' => 'throttle-'.uniqid().'@example.test']]],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testLoginRequestIsThrottledPerEmailAcrossDifferentIps(): void
    {
        self::getContainer()->get('cache.login_request_email_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        // A fixed target email requested from a different IP each time: the
        // per-IP limiter alone would never trip for an IP-rotating attacker.
        $targetEmail = 'flood-target-'.uniqid().'@example.test';

        // login_request_email limiter: fixed_window, limit 5 per hour.
        for ($i = 0; $i < 5; ++$i) {
            $response = $client->request('POST', '/rz-admin/login/request', [
                'headers' => ['REMOTE_ADDR' => $this->randomIp()],
                'extra' => ['parameters' => ['login_request' => ['email' => $targetEmail]]],
            ]);
            self::assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $client->request('POST', '/rz-admin/login/request', [
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
            'extra' => ['parameters' => ['login_request' => ['email' => $targetEmail]]],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testLoginResetIsThrottledPerIp(): void
    {
        self::getContainer()->get('cache.login_reset_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $fixedIp = '203.0.113.11';

        // login_reset limiter: token_bucket, limit 3, refilling 3/minute => burst capacity 3.
        // A GET with a bogus token already reaches the limiter (it guards
        // against blind token-guessing, not just form submission).
        for ($i = 0; $i < 3; ++$i) {
            $response = $client->request('GET', '/rz-admin/login/reset/bogus-token-'.uniqid(), [
                'headers' => ['REMOTE_ADDR' => $fixedIp],
            ]);
            self::assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $client->request('GET', '/rz-admin/login/reset/bogus-token-'.uniqid(), [
            'headers' => ['REMOTE_ADDR' => $fixedIp],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testLoginLinkRequestIsThrottledPerIp(): void
    {
        self::getContainer()->get('cache.login_link_request_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $fixedIp = '203.0.113.12';

        // login_link_request limiter: token_bucket, limit 3, refilling 3/minute => burst capacity 3.
        for ($i = 0; $i < 3; ++$i) {
            $response = $client->request('POST', '/rz-admin/login_link', [
                'headers' => ['REMOTE_ADDR' => $fixedIp],
                'extra' => ['parameters' => ['email' => 'throttle-'.uniqid().'@example.test']],
            ]);
            self::assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $client->request('POST', '/rz-admin/login_link', [
            'headers' => ['REMOTE_ADDR' => $fixedIp],
            'extra' => ['parameters' => ['email' => 'throttle-'.uniqid().'@example.test']],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    public function testLoginLinkRequestIsThrottledPerEmailAcrossDifferentIps(): void
    {
        self::getContainer()->get('cache.login_link_request_email_limiter')->clear();

        $client = self::createClient();
        $client->disableReboot();
        $targetEmail = 'flood-target-'.uniqid().'@example.test';

        // login_link_request_email limiter: fixed_window, limit 5 per hour.
        for ($i = 0; $i < 5; ++$i) {
            $response = $client->request('POST', '/rz-admin/login_link', [
                'headers' => ['REMOTE_ADDR' => $this->randomIp()],
                'extra' => ['parameters' => ['email' => $targetEmail]],
            ]);
            self::assertNotSame(429, $response->getStatusCode());
        }

        $throttled = $client->request('POST', '/rz-admin/login_link', [
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
            'extra' => ['parameters' => ['email' => $targetEmail]],
        ]);
        self::assertSame(429, $throttled->getStatusCode());
    }

    private function randomIp(): string
    {
        return sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254));
    }
}
