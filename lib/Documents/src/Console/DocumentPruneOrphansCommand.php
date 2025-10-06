<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DocumentPruneOrphansCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('documents:prune:orphans')
            ->setDescription('Remove any document without existing file on filesystem, except embeds. <info>Danger zone</info>')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Just list documents with invalid file and their problem, do not delete them')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->getManager();
        $this->io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        if ($dryRun) {
            $this->io->note('Dry run');
        }
        $deleteCount = 0;
        $pathsToPrune = [];

        $this->onEachDocument(function (DocumentInterface $document) use ($em, &$deleteCount, $dryRun, &$pathsToPrune) {
            $this->checkDocumentFilesystem($document, $em, $deleteCount, (bool) $dryRun, $pathsToPrune);
        }, new SymfonyStyle($input, $output));

        if (!$output->isQuiet()) {
            $this->io->table(
                ['Path to prune', 'Reason'],
                $pathsToPrune,
            );
        }

        if (!$dryRun) {
            $this->io->success(sprintf('%d documents were deleted.', $deleteCount));
        } else {
            $this->io->warning(sprintf('%d documents would be deleted without --dry-run.', $deleteCount));
        }

        return Command::SUCCESS;
    }

    /**
     * @throws FilesystemException
     */
    private function checkDocumentFilesystem(
        DocumentInterface $document,
        ObjectManager $entityManager,
        int &$deleteCount,
        bool $dryRun = false,
        array &$pathsToPrune = [],
    ): void {
        /*
         * Do not prune embed documents which may not have any file
         */
        $mountPath = $document->getMountPath();
        if (null === $mountPath || $document->isEmbed()) {
            return;
        }

        $fileExists = $this->documentsStorage->fileExists($mountPath);

        if ($fileExists && $this->documentsStorage->fileSize($mountPath) > 0) {
            return;
        }

        $pathsToPrune[] = [
            'path' => $mountPath,
            'reason' => !$fileExists ? 'File does not exist' : 'File size is 0 bytes',
        ];
        ++$deleteCount;

        if (!$dryRun) {
            $entityManager->remove($document);
        }
    }
}
