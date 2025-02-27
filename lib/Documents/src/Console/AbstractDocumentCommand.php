<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Repository\DocumentRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractDocumentCommand extends Command
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected ImageManager $imageManager,
        protected FilesystemOperator $documentsStorage,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function getManager(): ObjectManager
    {
        return $this->managerRegistry->getManager();
    }

    /**
     * @return DocumentRepositoryInterface<DocumentInterface>&EntityRepository<DocumentInterface>
     */
    protected function getDocumentRepository(): DocumentRepositoryInterface
    {
        $repository = $this->managerRegistry->getRepository(DocumentInterface::class);
        if (!$repository instanceof DocumentRepositoryInterface) {
            throw new \InvalidArgumentException('Document repository must implement '.DocumentRepositoryInterface::class);
        }

        return $repository;
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function onEachDocument(callable $method, SymfonyStyle $io, int $batchSize = 20): int
    {
        $i = 0;
        $manager = $this->getManager();
        $count = intval($this->getDocumentRepository()
            ->createQueryBuilder('d')
            ->select('count(d)')
            ->getQuery()
            ->getSingleScalarResult());

        if ($count < 1) {
            $io->success('No document found');

            return 0;
        }

        $q = $this->getDocumentRepository()
            ->createQueryBuilder('d')
            ->getQuery();
        $iterableResult = $q->toIterable();

        $io->progressStart($count);
        foreach ($iterableResult as $document) {
            $method($document);
            if (($i % $batchSize) === 0) {
                $manager->flush(); // Executes all updates.
                $manager->clear(); // Detaches all objects from Doctrine!
            }
            ++$i;
            $io->progressAdvance();
        }
        $manager->flush();
        $io->progressFinish();

        return 0;
    }
}
