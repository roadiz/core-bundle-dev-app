<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Documents\MediaFinders\FacebookPictureFinder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidFacebookNameValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ValidFacebookName $constraint
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value != "") {
            if (0 === preg_match("#^[0-9]*$#", $value)) {
                $this->context->addViolation($constraint->message);
            } else {
                /*
                 * Test if the username really exists.
                 */
                $facebook = new FacebookPictureFinder($value);
                try {
                    $facebook->getPictureUrl();
                } catch (\Exception $e) {
                    $this->context->addViolation($constraint->message);
                }
            }
        }
    }
}
