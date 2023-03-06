<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use JsonException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ValidJsonValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ValidJson $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!empty($value)) {
            try {
                \json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->context->addViolation($constraint->message, [
                    '{{ error }}' => $e->getMessage()
                ]);
            }
        }
    }
}
