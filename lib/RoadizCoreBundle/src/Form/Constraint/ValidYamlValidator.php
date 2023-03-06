<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ValidYamlValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ValidYaml $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value != "") {
            try {
                if (is_array($value)) {
                    // value already has been parsed into array
                    return;
                }
                Yaml::parse($value);
            } catch (ParseException $e) {
                $this->context->addViolation($constraint->message, [
                    '{{ error }}' => $e->getMessage()
                ]);
            }
        }
    }
}
