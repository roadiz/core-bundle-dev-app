<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;

final readonly class EntityGeneratorFactory
{
    public function __construct(
        private NodeTypeResolverInterface $nodeTypeResolverBag,
        private DefaultValuesResolverInterface $defaultValuesResolver,
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        private array $options,
    ) {
    }

    public function create(NodeTypeInterface $nodeType): EntityGeneratorInterface
    {
        return new EntityGenerator($nodeType, $this->nodeTypeResolverBag, $this->defaultValuesResolver, $this->nodeTypeClassLocator, $this->options);
    }

    public function createWithCustomRepository(NodeTypeInterface $nodeType): EntityGeneratorInterface
    {
        $options = $this->options;
        $options['repository_class'] = $this->nodeTypeClassLocator->getRepositoryFullQualifiedClassName($nodeType);

        return new EntityGenerator($nodeType, $this->nodeTypeResolverBag, $this->defaultValuesResolver, $this->nodeTypeClassLocator, $options);
    }

    public function createCustomRepository(NodeTypeInterface $nodeType): RepositoryGeneratorInterface
    {
        $options = [
            'entity_namespace' => $this->nodeTypeClassLocator->getClassNamespace(),
            'parent_class' => \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository::class,
        ];
        $options['namespace'] = $this->nodeTypeClassLocator->getRepositoryNamespace();
        $options['class_name'] = $this->nodeTypeClassLocator->getRepositoryClassName($nodeType);

        return new RepositoryGenerator($nodeType, $this->nodeTypeClassLocator, $options);
    }
}
