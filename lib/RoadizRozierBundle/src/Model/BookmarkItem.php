<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

final class BookmarkItem
{
    public function __construct(
        private readonly string $label,
        private readonly string $url,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}