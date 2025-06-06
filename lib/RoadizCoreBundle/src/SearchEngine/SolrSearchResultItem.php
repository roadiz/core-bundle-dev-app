<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @template T of object
 */
#[ApiResource(
    stateless: true,
)]
#[Exclude]
final readonly class SolrSearchResultItem implements SolrSearchResultItemInterface
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
    #[\Override]
    public function getItem(): object
    {
        return $this->item;
    }

    /**
     * @return array<string, array<string>>
     */
    #[ApiProperty]
    #[Groups(['get'])]
    #[\Override]
    public function getHighlighting(): array
    {
        return $this->highlighting;
    }
}
