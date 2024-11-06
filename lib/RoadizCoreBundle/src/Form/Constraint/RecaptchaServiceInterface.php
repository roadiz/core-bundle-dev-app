<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

interface RecaptchaServiceInterface
{
    /**
     * Makes a request to recaptcha service and checks if recaptcha field is valid.
     * Returns Google error-codes if recaptcha fails.
     *
     * @return true|mixed
     */
    public function check(
        string $responseValue,
        string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify',
    ): mixed;
}
