<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant\Exception;

/**
 * The credentials are rejected: wrong or revoked API key, blocked or unpaid account.
 *
 * Never retry: only a configuration or billing change can fix it.
 */
final class TranslateAssistantAccountException extends TranslateAssistantException
{
}
