<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait CaptchaProtectedTrait
{
    abstract protected function getCaptchaService(): CaptchaServiceInterface;

    protected function validateCaptchaHeader(Request $request): void
    {
        /*
         * Generate a header name based on the captcha service field name.
         * x-g-recaptcha-response, x-frc-captcha-response for example.
         */
        $captchaHeaderName = mb_strtolower('X-'.$this->getCaptchaService()->getFieldName());

        $responseValue = $request->headers->get($captchaHeaderName, null);
        if (null === $responseValue) {
            throw new BadRequestHttpException(sprintf('You must provide "%s" header for human verification.', $captchaHeaderName));
        }
        if (true !== $response = $this->getCaptchaService()->check($responseValue)) {
            if (\is_string($response)) {
                throw new BadRequestHttpException($captchaHeaderName.': '.$response);
            } elseif (\is_array($response)) {
                throw new BadRequestHttpException($captchaHeaderName.': '.reset($response));
            }
            throw new BadRequestHttpException($captchaHeaderName.': captcha response is not valid.');
        }
    }
}
