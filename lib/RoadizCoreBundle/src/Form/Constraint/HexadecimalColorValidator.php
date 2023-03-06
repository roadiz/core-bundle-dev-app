<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HexadecimalColorValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof HexadecimalColor) {
            if (null !== $value && preg_match('#\#[0-9a-f]{6}#', strtolower($value)) === 0) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
