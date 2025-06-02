<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidEmbedId extends \InvalidArgumentException
{
    public function __construct(protected ?string $embedId = null, protected ?string $platform = null)
    {
        parent::__construct('Embed ID is not valid for this platform', Response::HTTP_BAD_REQUEST);
    }

    public function getEmbedId(): ?string
    {
        return $this->embedId;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }
}
