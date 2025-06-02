<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentFilesizeCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    #[\Override]
    protected function configure(): void
    {
        $this->setName('documents:file:size')
            ->setDescription('Fetch every document file size (in bytes) and write it in database.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        return $this->onEachDocument(function (DocumentInterface $document) {
            if ($document instanceof AdvancedDocumentInterface) {
                $this->updateDocumentFilesize($document);
            }
        }, new SymfonyStyle($input, $output));
    }

    private function updateDocumentFilesize(AdvancedDocumentInterface $document): void
    {
        if (null === $document->getMountPath()) {
            return;
        }
        try {
            $document->setFilesize($this->documentsStorage->fileSize($document->getMountPath()));
        } catch (FilesystemException $exception) {
            $this->io->error($exception->getMessage());
        }
    }
}
