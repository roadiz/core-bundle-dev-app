<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Captcha;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Captcha\CapCaptchaService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CapCaptchaServiceTest extends TestCase
{
    private const VERIFY_URL = 'https://cap.example.com/d9256640cb53/siteverify';

    public function testCheckPostsJsonAndSucceeds(): void
    {
        $captured = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? null];

            return new MockResponse('{"success":true}');
        });

        $service = new CapCaptchaService($client, 'secret-key', self::VERIFY_URL);
        $result = $service->check('a-token');

        self::assertTrue($result);
        self::assertSame('POST', $captured['method']);
        self::assertSame(self::VERIFY_URL, $captured['url']);
        // 'json' option is serialized into the request body with a JSON content-type.
        self::assertSame(['secret' => 'secret-key', 'response' => 'a-token'], json_decode((string) $captured['body'], true));
    }

    public function testCheckFailsOnUnsuccessfulResponse(): void
    {
        $client = new MockHttpClient(new MockResponse('{"success":false}'));
        $service = new CapCaptchaService($client, 'secret-key', self::VERIFY_URL);

        self::assertSame('captcha_is_invalid', $service->check('a-token'));
    }

    public function testDisabledServiceShortCircuits(): void
    {
        $service = new CapCaptchaService(new MockHttpClient(), null, '');

        self::assertFalse($service->isEnabled());
        self::assertTrue($service->check('whatever'));
    }

    public function testPublicKeyDerivesWidgetEndpointFromVerifyUrl(): void
    {
        $service = new CapCaptchaService(new MockHttpClient(), 'secret-key', self::VERIFY_URL);

        self::assertSame('https://cap.example.com/d9256640cb53/', $service->getPublicKey());
    }
}
