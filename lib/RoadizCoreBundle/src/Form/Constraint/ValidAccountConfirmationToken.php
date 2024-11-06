<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class ValidAccountConfirmationToken extends Constraint
{
    /**
     * Confirmation token time to live, in seconds.
     */
    public int $ttl = 60;

    public string $message = 'confirmation.token.is.invalid';

    public string $expiredMessage = 'confirmation.token.has.expired';
}
