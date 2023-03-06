<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentPruneCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('documents:prune:unused')
            ->setDescription('Delete every document not used by a setting, a node-source, a tag or an attribute. <info>Danger zone</info>')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not delete any document, just display unused count.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $batchSize = 20;
        $i = 0;

        $em = $this->getManager();
        $documents = $this->getDocumentRepository()->findAllUnused();
        $count = \count($documents);

        if ($count <= 0) {
            $this->io->warning('All documents are used.');
            return 0;
        }

        if ($input->getOption('dry-run')) {
            $this->io->info(sprintf(
                '%d documents are not used by a node-source, a tag, a setting or an attribute.',
                $count
            ));
            return 0;
        }

        if (
            $this->io->askQuestion(new ConfirmationQuestion(
                sprintf('Are you sure to delete permanently %d unused documents?', $count),
                false
            ))
        ) {
            $this->io->progressStart($count);
            /** @var DocumentInterface $document */
            foreach ($documents as $document) {
                $em->remove($document);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                }
                ++$i;
                $this->io->progressAdvance();
            }
            $em->flush();
            $this->io->progressFinish();
        }

        return 0;
    }
}
