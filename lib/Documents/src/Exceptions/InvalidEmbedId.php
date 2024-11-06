<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidEmbedId extends \InvalidArgumentException
{
    protected ?string $embedId;
    protected ?string $platform;

    public function __construct(?string $embedId = null, ?string $platform = null)
    {
        parent::__construct('Embed ID is not valid for this platform', Response::HTTP_BAD_REQUEST);
        $this->embedId = $embedId;
        $this->platform = $platform;
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
