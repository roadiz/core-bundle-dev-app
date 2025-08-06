<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ValidHotspotJsonValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!is_array($value)) {
            $this->context->addViolation($constraint->message);

            return;
        }

        if (!array_key_exists('x', $value) || !array_key_exists('y', $value)) {
            $this->context->addViolation('hotspot_does_not_contain_x_and_y');

            return;
        }

        if (!is_numeric($value['x']) || !is_numeric($value['y'])) {
            $this->context->addViolation('hotspot_x_and_y_must_be_numeric');
        }

        if ($value['x'] < 0 || $value['x'] > 1) {
            $this->context->addViolation('hotspot_x_must_be_between_0_and_1');
        }

        if ($value['y'] < 0 || $value['y'] > 1) {
            $this->context->addViolation('hotspot_y_must_be_between_0_and_1');
        }

        /*
         * If areaStartX, areaStartY, areaEndX and areaEndY are present,
         * they must be numeric and between 0 and 1.
         */
        if (
            array_key_exists('areaStartX', $value)
            || array_key_exists('areaStartY', $value)
            || array_key_exists('areaEndX', $value)
            || array_key_exists('areaEndY', $value)
        ) {
            $props = [
                'areaStartX',
                'areaStartY',
                'areaEndX',
                'areaEndY',
            ];

            foreach ($props as $propName) {
                if (!array_key_exists($propName, $value)) {
                    $this->context->addViolation('hotspot_must_contain_area_start_and_end_coordinates');

                    return;
                }
                if (!is_numeric($value[$propName])) {
                    $this->context->addViolation('hotspot_area_start_and_end_coordinates_must_be_numeric');

                    return;
                }
                if ($value[$propName] < 0 || $value[$propName] > 1) {
                    $this->context->addViolation('hotspot_area_start_and_end_coordinates_must_be_between_0_and_1');

                    return;
                }
            }
        }
    }
}
