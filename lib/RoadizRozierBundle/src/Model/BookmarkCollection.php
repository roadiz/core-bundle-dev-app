<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

final readonly class BookmarkCollection
{
    /**
     * @param BookmarkItem[] $bookmarkItems
     */
    public function __construct(private array $bookmarkItems = [])
    {
    }

    /**
     * @return BookmarkItem[]
     */
    public function getBookmarkItems(): array
    {
        return $this->bookmarkItems;
    }

    /**
     * @param array<array<string, string>> $config
     */
    public static function fromConfiguration(array $config): self
    {
        $collection = [];

        foreach ($config as $bookmarkConfig) {
            if (is_array($bookmarkConfig) && isset($bookmarkConfig['label'], $bookmarkConfig['url'])) {
                $collection[] = new BookmarkItem(
                    (string) $bookmarkConfig['label'],
                    (string) $bookmarkConfig['url']
                );
            }
        }

        return new self($collection);
    }
}
