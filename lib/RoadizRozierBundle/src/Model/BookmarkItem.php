<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

final readonly class BookmarkItem
{
    private string $url;
    private string $label;

    public function __construct(
        string $label,
        string $url,
    ) {
        if (false === $validUrl = filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('The URL %s is not valid.', $url));
        }
        $this->url = $validUrl;

        $label = trim(strip_tags($label));
        if (empty($label)) {
            throw new \InvalidArgumentException('Label cannot be empty.');
        }
        $this->label = $label;
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
