<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait RecaptchaProtectedControllerTrait
{
    abstract protected function getRecaptchaHeaderName(): string;
    abstract protected function getRecaptchaService(): RecaptchaServiceInterface;

    protected function validateRecaptchaHeader(Request $request): void
    {
        $responseValue = $request->headers->get($this->getRecaptchaHeaderName(), null);
        if (null === $responseValue) {
            throw new BadRequestHttpException(sprintf('You must provide %s header for human verification.', $this->getRecaptchaHeaderName()));
        }
        if (true !== $response = $this->getRecaptchaService()->check($responseValue)) {
            if (\is_string($response)) {
                throw new BadRequestHttpException($this->getRecaptchaHeaderName() . ': ' . $response);
            } elseif (\is_array($response)) {
                throw new BadRequestHttpException($this->getRecaptchaHeaderName() . ': ' . reset($response));
            }
            throw new BadRequestHttpException($this->getRecaptchaHeaderName() . ': Recaptcha response is not valid.');
        }
    }
}
