<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;

class IndexerFactory implements IndexerFactoryInterface
{
    protected ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param class-string $classname
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getIndexerFor(string $classname): Indexer
    {
        return match ($classname) {
            Node::class => $this->serviceLocator->get(NodeIndexer::class),
            NodesSources::class => $this->serviceLocator->get(NodesSourcesIndexer::class),
            Document::class => $this->serviceLocator->get(DocumentIndexer::class),
            Tag::class => $this->serviceLocator->get(TagIndexer::class),
            Folder::class => $this->serviceLocator->get(FolderIndexer::class),
            default => throw new \LogicException(sprintf('No indexer found for "%s"', $classname)),
        };
    }
}
