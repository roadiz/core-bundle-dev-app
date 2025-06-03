<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Repository\StackTypeRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: StackTypeRepository::class),
    ORM\Table(name: 'stack_types'),
]
class StackType
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'stackTypes')]
        #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private Node $node,
        #[ORM\Id]
        #[ORM\Column(name: 'nodetype_name', type: 'string', length: 30, nullable: false)]
        #[Assert\Length(max: 30)]
        private string $nodeTypeName,
    ) {
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }

    public function setNodeTypeName(string $nodeTypeName): void
    {
        $this->nodeTypeName = $nodeTypeName;
    }
}
