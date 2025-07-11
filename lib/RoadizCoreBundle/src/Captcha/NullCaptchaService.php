<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

final readonly class NullCaptchaService implements CaptchaServiceInterface
{
    #[\Override]
    public function getFieldName(): string
    {
        return 'null_captcha';
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return false;
    }

    #[\Override]
    public function getPublicKey(): ?string
    {
        return null;
    }

    #[\Override]
    public function getFormWidgetName(): string
    {
        return 'null_captcha';
    }

    #[\Override]
    public function check(string $responseValue): true|string|array
    {
        return true;
    }
}
