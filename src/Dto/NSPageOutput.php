<?php

declare(strict_types=1);

namespace App\Dto;

use RZ\Roadiz\CoreBundle\Api\Dto\NodesSourcesDto;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Serializer\Annotation\Groups;

final class NSPageOutput extends NodesSourcesDto
{
    /**
     * @var array<Document>
     * @Groups({"nodes_sources", "nodes_sources_base"})
     */
    public array $images = [];
    /**
     * @var array<NodesSources>
     * @Groups({"nodes_sources", "nodes_sources_base"})
     */
    public array $nodeReferences = [];
    /**
     * @Groups({"nodes_sources", "nodes_sources_base"})
     */
    public ?string $content = null;
}
