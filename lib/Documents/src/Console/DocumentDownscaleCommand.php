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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Command line utils for process document downscale.
 */
class DocumentDownscaleCommand extends AbstractDocumentCommand
{
    private ?int $maxPixelSize;
    private DownscaleImageManager $downscaler;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ImageManager $imageManager,
        FilesystemOperator $documentsStorage,
        ?int $maxPixelSize,
        DownscaleImageManager $downscaler,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($managerRegistry, $imageManager, $documentsStorage);
        $this->maxPixelSize = $maxPixelSize;
        $this->downscaler = $downscaler;
        $this->dispatcher = $dispatcher;
    }

    protected function configure(): void
    {
        $this->setName('documents:downscale')
            ->setDescription('Downscale every document according to max pixel size defined in configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null !== $this->maxPixelSize && $this->maxPixelSize > 0) {
            $confirmation = new ConfirmationQuestion(
                '<question>Are you sure to downscale all your image documents to ' . $this->maxPixelSize . 'px?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $confirmation
                )
            ) {
                /** @var DocumentInterface[] $documents */
                $documents = $this->getDocumentRepository()
                    ->findBy([
                        'mimeType' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                            'image/tiff',
                        ],
                        'raw' => false,
                    ]);
                $io->progressStart(\count($documents));

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
            }
            return 0;
        } else {
            $io->warning('Your configuration is not set for downscaling documents.');
            $io->note('Add <info>assetsProcessing.maxPixelSize</info> parameter in your <info>config.yml</info> file.');
            return 1;
        }
    }
}
