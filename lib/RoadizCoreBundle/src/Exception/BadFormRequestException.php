<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

/**
 * Exception raised when a form is sent without the right parameters.
 */
class BadFormRequestException extends \Exception
{
    protected string $statusText;
    protected ?string $fieldErrored;

    /**
     * @param string|null $message
     * @param int $code
     * @param string $statusText
     * @param string|null $fieldErrored
     */
    public function __construct(?string $message = null, int $code = 403, string $statusText = 'danger', ?string $fieldErrored = null)
    {
        parent::__construct($message, $code);

        $this->statusText = $statusText;
        $this->fieldErrored = $fieldErrored;
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
