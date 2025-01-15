<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodeTypeExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly NodeType $nodeType,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getId(): string|int
    {
        return $this->nodeType->getId();
    }

    public function getAlternativeDisplayable(): ?string
    {
        return $this->nodeType->getName();
    }

    public function getNodeTypeName(): ?string
    {
        return $this->nodeType->getName();
    }

    public function getDisplayable(): string
    {
        return $this->nodeType->getDisplayName();
    }

    public function getOriginal(): NodeType
    {
        return $this->nodeType;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('nodeTypesEditPage', [
            'nodeTypeId' => $this->nodeType->getId(),
        ]);
    }

    protected function getColor(): ?string
    {
        return $this->nodeType->getColor();
    }



    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'nodeTypeName' => $this->getNodeTypeName(),
        ];
    }
}
