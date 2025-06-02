<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NonSqlReservedWordValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null !== $value) {
            $fieldName = StringHandler::variablize($value);
            $lowerName = \mb_strtolower((string) $value);
            if (
                in_array($value, NonSqlReservedWord::$forbiddenNames)
                || in_array($lowerName, NonSqlReservedWord::$forbiddenNames)
                || in_array($fieldName, NonSqlReservedWord::$forbiddenNames)
            ) {
                if ($constraint instanceof NonSqlReservedWord) {
                    $this->context->addViolation($constraint->message);
                }
            }
        }
    }
}
