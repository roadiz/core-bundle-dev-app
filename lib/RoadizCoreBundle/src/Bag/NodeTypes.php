<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepository;

final class NodeTypes extends LazyParameterBag implements NodeTypeResolverInterface
{
    private ?NodeTypeRepository $repository = null;

    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    public function getRepository(): NodeTypeRepository
    {
        if (null === $this->repository) {
            $this->repository = $this->managerRegistry->getRepository(NodeType::class);
        }

        return $this->repository;
    }

    protected function populateParameters(): void
    {
        try {
            $nodeTypes = $this->getRepository()->findAll();
            $this->parameters = [];
            /** @var NodeType $nodeType */
            foreach ($nodeTypes as $nodeType) {
                $this->parameters[$nodeType->getName()] = $nodeType;
                $this->parameters[$nodeType->getSourceEntityFullQualifiedClassName()] = $nodeType;
            }
        } catch (\Exception $e) {
            $this->parameters = [];
        }
        $this->ready = true;
    }
}
