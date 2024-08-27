<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\DownscaleImageManager;
use RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpSubprocess;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Command line utils for process document downscale.
 */
class DocumentDownscaleCommand extends AbstractDocumentCommand
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        ImageManager $imageManager,
        FilesystemOperator $documentsStorage,
        private readonly ?int $maxPixelSize,
        private readonly DownscaleImageManager $downscaler,
        private readonly EventDispatcherInterface $dispatcher,
        ?string $name = null
    ) {
        parent::__construct($managerRegistry, $imageManager, $documentsStorage, $name);
    }

    protected function configure(): void
    {
        $this->setName('documents:downscale')
            ->addOption('process-count', 'p', InputOption::VALUE_REQUIRED, 'Number of processes to run in parallel.', 1)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Number of document to process in one process', null)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Offset number of document to process after', null)
            ->setDescription('Downscale every document according to max pixel size defined in configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $this->maxPixelSize || $this->maxPixelSize <= 0) {
            $io->warning('Your configuration is not set for downscaling documents.');
            $io->note('Add <info>assetsProcessing.maxPixelSize</info> parameter in your <info>config.yml</info> file.');
            return 1;
        }

        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure to downscale all your image documents to ' . $this->maxPixelSize . 'px?</question>',
            false
        );
        if ($input->isInteractive() && !$io->askQuestion($confirmation)) {
            return 0;
        }

        $criteria = [
            'mimeType' => [
                'image/avif',
                'image/bmp',
                'image/gif',
                'image/heic',
                'image/heif',
                'image/jpeg',
                'image/png',
                'image/tiff',
                'image/webp',
            ],
            'raw' => false,
        ];
        $processCount = (int) $input->getOption('process-count');
        /*
         * Switch to async processes to batch document downscaling.
         */
        if ($processCount > 1) {
            $documentsCount = $this->getDocumentRepository()->countBy($criteria);
            $io->info(sprintf('Using %d processes to downscale %d documents.', $processCount, $documentsCount));

            // Spawn processes for current command with limit and offset parameters
            $documentsPerProcess = (int) ceil($documentsCount / $processCount);
            /** @var array<PhpSubprocess> $processes */
            $processes = [];

            for ($i = 0; $i < $processCount; $i++) {
                $offset = $i * $documentsPerProcess;
                $limit = $documentsPerProcess;

                $command = [
                    'bin/console',
                    'documents:downscale',
                    '-n',
                    '--process-count=1',
                    '--limit=' . $limit,
                    '--offset=' . $offset,
                ];

                $process = new PhpSubprocess($command);
                $process->setTimeout(3600);
                $process->start();
                $processes[] = $process;

                $io->text(sprintf('Started documents:downscale process %d with offset %d and limit %d', $i + 1, $offset, $limit));
            }
            // Wait for all processes to finish
            foreach ($processes as $process) {
                $process->wait();
            }

            $io->success('All processes have finished.');
            return 0;
        }

        /** @var DocumentInterface[] $documents */
        $documents = $this->getDocumentRepository()->findBy(
            $criteria,
            [],
            is_numeric($input->getOption('limit')) ? (int) $input->getOption('limit') : null,
            is_numeric($input->getOption('offset')) ? (int) $input->getOption('offset') : null
        );
        $io->progressStart(count($documents));

        foreach ($documents as $document) {
            try {
                $this->downscaler->processDocumentFromExistingRaw($document);
            } catch (NotReadableException $exception) {
                $io->error($exception->getMessage() . ' - ' . (string) $document);
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Every documents have been downscaled, a raw version has been kept.');

        $this->dispatcher->dispatch(new CachePurgeAssetsRequestEvent());
        return 0;
    }
}
