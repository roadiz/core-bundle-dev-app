<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant\Exception;

/**
 * Base class for every provider failure, so callers never need to know which provider is wired.
 *
 * Thrown as-is when a failure fits none of the subclasses. Treat it as permanent: retrying an
 * unclassified failure usually just burns the same call again.
 */
class TranslateAssistantException extends \RuntimeException
{
}
