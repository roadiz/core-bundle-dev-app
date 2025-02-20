<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @deprecated
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class ValidFacebookName extends Constraint
{
    public string $message = 'not.valid.facebook.name';
}
