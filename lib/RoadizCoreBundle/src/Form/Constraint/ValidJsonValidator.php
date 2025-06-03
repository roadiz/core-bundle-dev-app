<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ValidJsonValidator extends ConstraintValidator
{
    /**
     * @param ValidJson $constraint
     */
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!empty($value) && is_string($value)) {
            try {
                \json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->context->addViolation($constraint->message, [
                    '{{ error }}' => $e->getMessage(),
                ]);
            }
        }
    }
}
