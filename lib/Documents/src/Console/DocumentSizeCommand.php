<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Intervention\Image\Exceptions\DecoderException;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\SvgSizeResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentSizeCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('documents:size')
            ->setDescription('Fetch every document size (width and height) and write it in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        return $this->onEachDocument(function (DocumentInterface $document) {
            if ($document instanceof SizeableInterface) {
                $this->updateDocumentSize($document);
            }
        }, new SymfonyStyle($input, $output));
    }

    private function updateDocumentSize(DocumentInterface $document): void
    {
        if (!($document instanceof SizeableInterface)) {
            return;
        }
        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            return;
        }
        if ($document->isSvg()) {
            try {
                $svgSizeResolver = new SvgSizeResolver($document, $this->documentsStorage);
                $document->setImageWidth($svgSizeResolver->getWidth());
                $document->setImageHeight($svgSizeResolver->getHeight());
            } catch (\RuntimeException $exception) {
                $this->io->error($exception->getMessage());
            }
        } elseif ($document->isImage()) {
            try {
                $imageProcess = $this->imageManager->read($this->documentsStorage->readStream($mountPath));
                $document->setImageWidth($imageProcess->width());
                $document->setImageHeight($imageProcess->height());
            } catch (DecoderException|FilesystemException $exception) {
                /*
                 * Do nothing
                 * just return 0 width and height
                 */
                $this->io->error($document->getMountPath().' is not a readable image.');
            }
        }
    }
}
