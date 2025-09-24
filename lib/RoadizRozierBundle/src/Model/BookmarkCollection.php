<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

final class BookmarkCollection
{
    /** @var BookmarkItem[] */
    private array $bookmarkItems = [];

    /**
     * @param BookmarkItem[] $bookmarkItems
     */
    public function __construct(array $bookmarkItems = [])
    {
        foreach ($bookmarkItems as $bookmarkItem) {
            $this->addBookmarkItem($bookmarkItem);
        }
    }

    public function addBookmarkItem(BookmarkItem $bookmarkItem): self
    {
        $this->bookmarkItems[] = $bookmarkItem;
        return $this;
    }

    /**
     * @return BookmarkItem[]
     */
    public function getBookmarkItems(): array
    {
        return $this->bookmarkItems;
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromConfiguration(array $config): self
    {
        $collection = new self();
        
        foreach ($config as $bookmarkConfig) {
            if (is_array($bookmarkConfig) && isset($bookmarkConfig['label'], $bookmarkConfig['url'])) {
                $collection->addBookmarkItem(new BookmarkItem(
                    (string) $bookmarkConfig['label'],
                    (string) $bookmarkConfig['url']
                ));
            }
        }
        
        return $collection;
    }
}