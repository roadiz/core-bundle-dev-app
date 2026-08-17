<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;

/**
 * Functional coverage of security audit finding H1's CSRF sub-fix: the Dropzone
 * uploader (RzFileUpload.ts) must be able to actually authenticate its upload
 * requests against POST /rz-admin/documents/upload/json.
 *
 * A first fix sent the ajax token as an HTTP header ("_token"), matching what
 * the JS sent at the time — but nginx's default `underscores_in_headers off`
 * silently drops any header containing an underscore, so it never reached PHP
 * in a real deployment. Both the JS and the controller now use a `_token` form
 * field instead, which works regardless of proxy/webserver header handling.
 */
final class DocumentUploadCsrfTest extends ApiTestCase
{
    public function testUploadIsRejectedWithoutToken(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->loginBackendUser($client);

        $response = $client->request('POST', '/rz-admin/documents/upload/json', [
            'headers' => ['Accept' => 'application/json'],
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('csrf', strtolower($response->getContent(false)));
    }

    public function testUploadIsAcceptedWithTokenAsFormField(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->loginBackendUser($client);
        $token = $this->extractAjaxToken($client);

        $response = $client->request('POST', '/rz-admin/documents/upload/json', [
            'headers' => ['Accept' => 'application/json'],
            'extra' => ['parameters' => ['_token' => $token]],
        ]);

        // No attachment was actually sent, so this fails form validation —
        // the point here is only that it clears the CSRF gate (no 400 for it).
        self::assertNotSame(400, $response->getStatusCode());
    }

    private function loginBackendUser(mixed $client): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = new User();
        $user->setUsername('csrf-test-'.uniqid().'@test.test');
        $user->setEmail($user->getUsername());
        $user->setPassword('not-used');
        $user->setUserRoles(['ROLE_ACCESS_DOCUMENTS', 'ROLE_BACKEND_USER']);
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();

        $client->loginUser($user, 'main');
    }

    private function extractAjaxToken(mixed $client): string
    {
        $dashboard = $client->request('GET', '/rz-admin');
        self::assertSame(200, $dashboard->getStatusCode());

        preg_match("/ajaxToken['\"]?\s*:\s*'([^']+)'/", $dashboard->getContent(false), $matches);
        self::assertArrayHasKey(1, $matches, 'ajaxToken must be present in the rendered dashboard');

        return $matches[1];
    }
}
