<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Exception raised when no translation is available.
 */
class NoTranslationAvailableException extends ResourceNotFoundException
{
    /**
     * @var string
     */
    protected $message = 'No translation is available with your requested locale. Try an another locale or verify that your site has at least one available translation.';
}
