<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FileHashInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentFileHashCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    #[\Override]
    protected function configure(): void
    {
        $this->setName('documents:file:hash')
            ->setDescription('Compute every document file hash and store it.')
            ->addOption(
                'algorithm',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Hash algorithm (see https://www.php.net/manual/fr/function.hash-algos.php) <info>Default: sha256</info>'
            )
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $batchSize = 20;
        $i = 0;
        $defaultAlgorithm = $input->getOption('algorithm');
        if (!\is_string($defaultAlgorithm)) {
            $defaultAlgorithm = 'sha256';
        }
        if (!\in_array($defaultAlgorithm, \hash_algos())) {
            throw new \RuntimeException(sprintf('“%s” algorithm is not available. Choose one from \hash_algos() method (%s)', $defaultAlgorithm, implode(', ', \hash_algos())));
        }

        $em = $this->getManager();
        $documents = $this->getDocumentRepository()->findAllWithoutFileHash();
        $count = \count($documents);

        if ($count <= 0) {
            $this->io->success('All document files have hash.');

            return 0;
        }

        $this->io->progressStart($count);
        /** @var DocumentInterface $document */
        foreach ($documents as $document) {
            $mountPath = $document->getMountPath();
            if (null === $mountPath || !($document instanceof FileHashInterface)) {
                $this->io->progressAdvance();
                continue;
            }

            $algorithm = $document->getFileHashAlgorithm() ?? $defaultAlgorithm;
            // https://flysystem.thephpleague.com/docs/usage/checksums/
            $this->documentsStorage->checksum($mountPath, ['checksum_algo' => $algorithm]);
            if ($this->documentsStorage->fileExists($mountPath)) {
                $fileHash = $this->documentsStorage->checksum($mountPath, ['checksum_algo' => $algorithm]);
                $document->setFileHash($fileHash);
                $document->setFileHashAlgorithm($algorithm);
            }

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
