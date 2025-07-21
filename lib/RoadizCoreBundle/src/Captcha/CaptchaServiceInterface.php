<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

interface CaptchaServiceInterface
{
    public function getFieldName(): string;

    public function isEnabled(): bool;

    public function getPublicKey(): ?string;

    public function getFormWidgetName(): string;

    /**
     * Makes a request to a captcha service and checks if captcha response is valid.
     *
     * @return true|string|array True if captcha is valid, or an error message string or an array of error codes if captcha fails
     */
    public function check(
        string $responseValue,
    ): true|string|array;
}
