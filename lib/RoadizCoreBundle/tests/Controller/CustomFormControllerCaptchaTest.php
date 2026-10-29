<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;

/**
 * Functional coverage of security audit finding M8: POST /api/custom_forms/{id}/post
 * must go through the same CaptchaServiceInterface gate as the contact form.
 *
 * CustomFormsType::buildForm() already adds a CaptchaType field whenever
 * CaptchaServiceInterface::isEnabled() (see CustomFormsTypeTest for the
 * form-building unit test); this test proves the gate is enforced end to end
 * on the real HTTP endpoint: rejected without a valid response, accepted
 * once the (stubbed) captcha check passes.
 */
final class CustomFormControllerCaptchaTest extends ApiTestCase
{
    private const string CAPTCHA_FIELD = 'test-custom-form-captcha';

    public function testSubmissionWithInvalidCaptchaIsRejected(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->overrideCaptcha(accepts: false);
        $customForm = $this->createOpenCustomForm();

        $response = $client->request('POST', sprintf('/api/custom_forms/%d/post', $customForm->getId()), [
            'extra' => ['parameters' => [self::CAPTCHA_FIELD => 'invalid-response']],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testSubmissionWithValidCaptchaSucceeds(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->overrideCaptcha(accepts: true);
        $customForm = $this->createOpenCustomForm();

        $response = $client->request('POST', sprintf('/api/custom_forms/%d/post', $customForm->getId()), [
            'extra' => ['parameters' => [self::CAPTCHA_FIELD => 'valid-response']],
            'headers' => ['REMOTE_ADDR' => $this->randomIp()],
        ]);

        self::assertSame(202, $response->getStatusCode());
    }

    /**
     * Replaces the compiled CaptchaServiceInterface with an in-memory stub so
     * this test never depends on a real captcha provider being configured.
     */
    private function overrideCaptcha(bool $accepts): void
    {
        self::getContainer()->set(CaptchaServiceInterface::class, new class($accepts) implements CaptchaServiceInterface {
            public function __construct(private readonly bool $accepts)
            {
            }

            public function getFieldName(): string
            {
                return 'test-custom-form-captcha';
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
                return 'test-custom-form-captcha';
            }

            public function check(string $responseValue): true|string|array
            {
                return $this->accepts ? true : 'captcha_is_invalid.try_again';
            }
        });
    }

    private function createOpenCustomForm(): CustomForm
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $customForm = new CustomForm();
        $customForm->setDisplayName('Captcha test form '.uniqid());
        $customForm->setOpen(true);
        $customForm->setEmail('captcha-test@example.test');

        $em->persist($customForm);
        $em->flush();

        return $customForm;
    }

    private function randomIp(): string
    {
        return sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254));
    }
}
