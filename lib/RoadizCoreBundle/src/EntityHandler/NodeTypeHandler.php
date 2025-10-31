<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceGenerator;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodeRepository;
use RZ\Roadiz\EntityGenerator\EntityGeneratorFactory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Handle operations with node-type entities.
 */
final class NodeTypeHandler extends AbstractHandler
{
    private ?NodeType $nodeType = null;

    public function getNodeType(): NodeType
    {
        if (null === $this->nodeType) {
            throw new \BadMethodCallException('NodeType is null');
        }

        return $this->nodeType;
    }

    /**
     * @return $this
     */
    public function setNodeType(NodeType $nodeType): self
    {
        $this->nodeType = $nodeType;

        return $this;
    }

    public function __construct(
        ObjectManager $objectManager,
        private readonly EntityGeneratorFactory $entityGeneratorFactory,
        private readonly HandlerFactory $handlerFactory,
        private readonly ApiResourceGenerator $apiResourceGenerator,
        private readonly LoggerInterface $logger,
        private readonly NotPublishedNodeRepository $notPublishedNodeRepository,
        private readonly string $generatedEntitiesDir,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
        parent::__construct($objectManager);
    }

    public function getGeneratedEntitiesFolder(): string
    {
        return $this->generatedEntitiesDir;
    }

    public function getGeneratedRepositoriesFolder(): string
    {
        return $this->getGeneratedEntitiesFolder().DIRECTORY_SEPARATOR.'Repository';
    }

    /**
     * Remove node type entity class file from server.
     */
    public function removeSourceEntityClass(): bool
    {
        $file = $this->getSourceClassPath();
        $repositoryFile = $this->getRepositoryClassPath();
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($file) && is_file($file)) {
            $fileSystem->remove($file);
            /*
             * Delete repository class file too.
             */
            if ($fileSystem->exists($repositoryFile) && is_file($repositoryFile)) {
                $fileSystem->remove($repositoryFile);
            }
            $this->logger->info('Entity class file and repository have been removed.', [
                'nodeType' => $this->nodeType->getName(),
                'file' => $file,
                'repositoryFile' => $repositoryFile,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate Doctrine entity class for current node-type.
     */
    public function generateSourceEntityClass(): bool
    {
        $folder = $this->getGeneratedEntitiesFolder();
        $repositoryFolder = $this->getGeneratedRepositoriesFolder();
        $file = $this->getSourceClassPath();
        $repositoryFile = $this->getRepositoryClassPath();
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($folder)) {
            $fileSystem->mkdir($folder, 0775);
        }
        if (!$fileSystem->exists($repositoryFolder)) {
            $fileSystem->mkdir($repositoryFolder, 0775);
        }

        if (!$fileSystem->exists($file)) {
            $classGenerator = $this->entityGeneratorFactory->createWithCustomRepository($this->nodeType);
            $repositoryGenerator = $this->entityGeneratorFactory->createCustomRepository($this->nodeType);
            $content = $classGenerator->getClassContent();
            $repositoryContent = $repositoryGenerator->getClassContent();

            $fileSystem->dumpFile($file, $content);
            $fileSystem->dumpFile($repositoryFile, $repositoryContent);

            /*
             * Force Zend OPcache to reset file
             */
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
                opcache_invalidate($repositoryFile, true);
            }
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }

            \clearstatcache(true, $file);
            \clearstatcache(true, $repositoryFile);
            $this->logger->info('Entity class file and repository have been generated.', [
                'nodeType' => $this->nodeType->getName(),
                'file' => $file,
                'repositoryFile' => $repositoryFile,
            ]);

            return true;
        }

        return false;
    }

    public function getSourceClassPath(): string
    {
        $folder = $this->getGeneratedEntitiesFolder();

        return $folder.DIRECTORY_SEPARATOR.$this->nodeTypeClassLocator->getSourceEntityClassName($this->nodeType).'.php';
    }

    public function getRepositoryClassPath(): string
    {
        $folder = $this->getGeneratedRepositoriesFolder();

        return $folder.DIRECTORY_SEPARATOR.$this->nodeTypeClassLocator->getRepositoryClassName($this->nodeType).'.php';
    }

    /**
     * Clear doctrine metadata cache and
     * regenerate entity class file.
     *
     * @return $this
     */
    public function updateSchema(): NodeTypeHandler
    {
        $this->regenerateEntityClass();

        return $this;
    }

    /**
     * Delete and recreate entity class file.
     */
    public function regenerateEntityClass(): NodeTypeHandler
    {
        $this->removeSourceEntityClass();
        $this->generateSourceEntityClass();
        if (null !== $this->nodeType) {
            $this->apiResourceGenerator->generate($this->nodeType);
        }

        return $this;
    }

    /**
     * Delete node-type class from database.
     *
     * @return $this
     */
    public function deleteSchema(): NodeTypeHandler
    {
        if (null !== $this->nodeType) {
            $this->apiResourceGenerator->remove($this->nodeType);
        }
        $this->removeSourceEntityClass();

        return $this;
    }

    /**
     * Delete node-type inherited nodes and its database schema
     * before removing it from node-types table.
     *
     * @return $this
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function deleteWithAssociations(): NodeTypeHandler
    {
        /*
         * Delete every nodes
         */
        $nodes = $this->notPublishedNodeRepository
            ->findBy([
                'nodeTypeName' => $this->getNodeType()->getName(),
            ]);

        /** @var Node $node */
        foreach ($nodes as $node) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->removeWithChildrenAndAssociations();
        }

        /*
         * Remove node type
         */
        $this->objectManager->remove($this->getNodeType());
        $this->objectManager->flush();

        /*
         * Remove class and database table
         */
        $this->deleteSchema();

        return $this;
    }

    #[\Override]
    public function cleanPositions(bool $setPositions = false): float
    {
        throw new \LogicException('Node-types are static, you can not clean their positions.');
    }
}
