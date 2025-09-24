<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

final readonly class BookmarkItem
{
    public function __construct(
        private string $label,
        private string $url,
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