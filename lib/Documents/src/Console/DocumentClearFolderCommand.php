<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentClearFolderCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    #[\Override]
    protected function configure(): void
    {
        $this->setName('documents:clear-folder')
            ->addArgument('folderId', InputArgument::REQUIRED, 'Folder ID to delete documents from.')
            ->setDescription('Delete every document from folder. <info>Danger zone</info>')
        ;
    }

    protected function getDocumentQueryBuilder(FolderInterface $folder): QueryBuilder
    {
        $qb = $this->getDocumentRepository()->createQueryBuilder('d');

        return $qb->innerJoin('d.folders', 'f')
            ->andWhere($qb->expr()->eq('f.id', ':folderId'))
            ->setParameter(':folderId', $folder);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $folderId = $input->getArgument('folderId');

        if (!\is_numeric($folderId) || $folderId <= 0) {
            throw new \InvalidArgumentException('Folder ID must be a valid ID');
        }
        $em = $this->getManager();
        /** @var FolderInterface|null $folder */
        $folder = $em->find(FolderInterface::class, $folderId);
        if (null === $folder) {
            throw new \InvalidArgumentException(sprintf('Folder #%d does not exist.', $folderId));
        }

        $batchSize = 20;
        $i = 0;

        $count = intval($this->getDocumentQueryBuilder($folder)
            ->select('count(d)')
            ->getQuery()
            ->getSingleScalarResult());

        if ($count <= 0) {
            $this->io->warning('No documents were found in this folder.');

            return 0;
        }

        if (
            !$this->io->askQuestion(new ConfirmationQuestion(
                sprintf('Are you sure to delete permanently %d documents?', $count),
                false
            ))
        ) {
            return 0;
        }

        /** @var DocumentInterface[] $results */
        $results = $this->getDocumentQueryBuilder($folder)
            ->select('d')
            ->getQuery()
            ->getResult();

        $this->io->progressStart($count);
        foreach ($results as $document) {
            $em->remove($document);
            if (($i % $batchSize) === 0) {
                $em->flush(); // Executes all updates.
            }
            ++$i;
            $this->io->progressAdvance();
        }
        $em->flush();
        $this->io->progressFinish();

        return 0;
    }
}
