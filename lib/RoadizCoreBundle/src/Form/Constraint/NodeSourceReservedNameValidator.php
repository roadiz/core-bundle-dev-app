<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NodeSourceReservedNameValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }
        if (!$constraint instanceof NodeSourceReservedName) {
            return;
        }

        if (NodeSourceReservedName::isReserved((string) $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%name%', (string) $value)
                ->addViolation();
        }
    }
}
