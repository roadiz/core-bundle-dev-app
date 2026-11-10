<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Form;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Form\CaptchaType;
use RZ\Roadiz\CoreBundle\Form\CustomFormsType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Regression test for security audit M8: custom-form submissions must go
 * through the same CaptchaServiceInterface gate as the contact form.
 */
class CustomFormsTypeTest extends TestCase
{
    private function getOptions(): array
    {
        return [
            'customForm' => new CustomForm(),
            'forceExpanded' => false,
            'fileUploadMaxSize' => '10m',
        ];
    }

    public function testCaptchaFieldAddedWhenServiceEnabled(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('isEnabled')->willReturn(true);
        $captchaService->method('getFieldName')->willReturn('g-recaptcha-response');

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->with('g-recaptcha-response', CaptchaType::class)
            ->willReturn($builder);

        (new CustomFormsType($captchaService))->buildForm($builder, $this->getOptions());
    }

    public function testCaptchaFieldNotAddedWhenServiceDisabled(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('isEnabled')->willReturn(false);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        (new CustomFormsType($captchaService))->buildForm($builder, $this->getOptions());
    }
}
