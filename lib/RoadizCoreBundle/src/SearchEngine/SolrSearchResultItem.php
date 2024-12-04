<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @template T of object
 */
#[ApiResource(
    stateless: true,
)]
final readonly class SolrSearchResultItem
{
    /**
     * @param T                            $item
     * @param array<string, array<string>> $highlighting
     */
    public function __construct(
        private object $item,
        private array $highlighting = [],
    ) {
    }

    /**
     * @return T
     */
    #[ApiProperty]
    #[Groups(['get'])]
    public function getItem(): object
    {
        return $this->item;
    }

    /**
     * @return array<string, array<string>>
     */
    #[ApiProperty]
    #[Groups(['get'])]
    public function getHighlighting(): array
    {
        return $this->highlighting;
    }
}
