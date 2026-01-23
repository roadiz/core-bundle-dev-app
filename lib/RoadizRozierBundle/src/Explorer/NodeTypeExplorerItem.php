<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

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

    #[\Override]
    public function getId(): string
    {
        return $this->nodeType->getName();
    }

    #[\Override]
    public function getAlternativeDisplayable(): string
    {
        return $this->nodeType->getName();
    }

    public function getNodeTypeName(): string
    {
        return $this->nodeType->getName();
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->nodeType->getDisplayName();
    }

    #[\Override]
    public function getOriginal(): NodeType
    {
        return $this->nodeType;
    }

    #[\Override]
    protected function getEditItemPath(): string
    {
        return $this->urlGenerator->generate('nodeTypesEditPage', [
            'nodeTypeName' => $this->nodeType->getName(),
        ]);
    }

    #[\Override]
    protected function getColor(): ?string
    {
        return $this->nodeType->getColor();
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'nodeTypeName' => $this->getNodeTypeName(),
        ];
    }
}
