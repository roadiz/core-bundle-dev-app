<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Intervention\Image\Exceptions\DecoderException;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\AverageColorResolver;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentAverageColorCommand extends AbstractDocumentCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('documents:color')
            ->setDescription('Fetch every document medium color and write it in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        return $this->onEachDocument(function (DocumentInterface $document) {
            $this->updateDocumentColor($document);
        }, new SymfonyStyle($input, $output));
    }

    private function updateDocumentColor(DocumentInterface $document): void
    {
        if (!$document->isImage() || !($document instanceof AdvancedDocumentInterface)) {
            return;
        }

        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            return;
        }
        try {
            $mediumColor = (new AverageColorResolver())->getAverageColor($this->imageManager->read(
                $this->documentsStorage->readStream($mountPath)
            ));
            $document->setImageAverageColor($mediumColor);
        } catch (DecoderException|FilesystemException) {
            /*
             * Do nothing
             * just return 0 width and height
             */
            $this->io->error($mountPath.' is not a readable image.');
        }
    }
}
