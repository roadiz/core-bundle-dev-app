<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant\Exception;

/**
 * The provider quota is exhausted.
 *
 * Never retry: the quota will not refill within a retry window, and every attempt is billed.
 */
final class TranslateAssistantUsageException extends TranslateAssistantException
{
}
