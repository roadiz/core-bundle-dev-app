<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

final readonly class NullCaptchaService implements CaptchaServiceInterface
{
    public function getFieldName(): string
    {
        return 'null_captcha';
    }

    public function isEnabled(): bool
    {
        return false;
    }

    public function getPublicKey(): ?string
    {
        return null;
    }

    public function getFormWidgetName(): string
    {
        return 'null_captcha';
    }

    public function check(string $responseValue): true|string|array
    {
        return true;
    }
}
