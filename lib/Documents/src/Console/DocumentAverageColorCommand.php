<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Console;

use Intervention\Image\Exception\NotReadableException;
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

        $this->onEachDocument(function (DocumentInterface $document) {
            $this->updateDocumentColor($document);
        }, new SymfonyStyle($input, $output));

        return 0;
    }

    private function updateDocumentColor(DocumentInterface $document): void
    {
        if ($document->isImage() && $document instanceof AdvancedDocumentInterface) {
            $mountPath = $document->getMountPath();
            if (null === $mountPath) {
                return;
            }
            try {
                $mediumColor = (new AverageColorResolver())->getAverageColor($this->imageManager->make(
                    $this->documentsStorage->readStream($mountPath)
                ));
                $document->setImageAverageColor($mediumColor);
            } catch (NotReadableException $exception) {
                /*
                 * Do nothing
                 * just return 0 width and height
                 */
                $this->io->error($mountPath . ' is not a readable image.');
            }
        }
    }
}
