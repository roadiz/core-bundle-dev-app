<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
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
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
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

        $this->onEachDocument(function (DocumentInterface $document) use ($em, $deleteCount, $dryRun) {
            $this->checkDocumentFilesystem($document, $em, $deleteCount, (bool) $dryRun);
        }, new SymfonyStyle($input, $output));

        $this->io->success(sprintf('%d documents were deleted.', $deleteCount));

        return 0;
    }

    /**
     * @throws FilesystemException
     */
    private function checkDocumentFilesystem(
        DocumentInterface $document,
        ObjectManager $entityManager,
        int &$deleteCount,
        bool $dryRun = false,
    ): void {
        /*
         * Do not prune embed documents which may not have any file
         */
        $mountPath = $document->getMountPath();
        if (null === $mountPath || $document->isEmbed()) {
            return;
        }
        if ($this->documentsStorage->fileExists($mountPath)) {
            return;
        }

        if ($this->io->isDebug() && !$this->io->isQuiet()) {
            $this->io->writeln(sprintf(
                '%s file does not exist, pruning document %s',
                $document->getMountPath(),
                (string) $document
            ));
        }
        if (!$dryRun) {
            $entityManager->remove($document);
            ++$deleteCount;
        }
    }
}
