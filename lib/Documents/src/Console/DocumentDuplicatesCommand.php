<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FileHashInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentDuplicatesCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('documents:duplicates')
            ->setDescription('Find duplicated documents based on their file hash.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $documents = $this->getDocumentRepository()->findDuplicates();
        $count = \count($documents);
        $rows = [];

        if (0 === $count) {
            $this->io->success('No duplicated documents were found.');

            return 0;
        }

        /** @var DocumentInterface&FileHashInterface $document */
        foreach ($documents as $document) {
            $rows[] = [
                'ID' => (string) $document,
                'Filename' => $document->getFilename(),
                'Hash' => $document->getFileHash(),
                'Algo' => $document->getFileHashAlgorithm(),
            ];
        }

        $this->io->table([
            'ID', 'Filename', 'Hash', 'Algo',
        ], $rows);

        return 0;
    }
}
