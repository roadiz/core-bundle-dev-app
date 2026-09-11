<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant\Exception;

/**
 * The provider could not be reached, or asked us to slow down.
 *
 * The only retryable failure: the same call may well succeed later.
 */
final class TranslateAssistantTransportException extends TranslateAssistantException
{
}
