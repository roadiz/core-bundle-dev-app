<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UniqueNodeName extends Constraint
{
    public ?string $currentValue = null;
    public string $message = 'nodeName.alreadyExists';
    public string $messageUrlAlias = 'nodeName.alreadyExists.as.urlAlias';
}
