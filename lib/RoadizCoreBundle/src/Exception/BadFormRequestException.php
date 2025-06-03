<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

/**
 * Exception raised when a form is sent without the right parameters.
 */
class BadFormRequestException extends \Exception
{
    public function __construct(?string $message = null, int $code = 403, protected string $statusText = 'danger', protected ?string $fieldErrored = null)
    {
        parent::__construct($message, $code);
    }

    public function getStatusText(): string
    {
        return $this->statusText;
    }

    public function getFieldErrored(): ?string
    {
        return $this->fieldErrored;
    }
}
