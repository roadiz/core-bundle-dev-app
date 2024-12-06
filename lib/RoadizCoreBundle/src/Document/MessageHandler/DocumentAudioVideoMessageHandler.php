<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Do all Audio/Video processing in one Message handling to avoid streaming media more than once.
 *
 * Detect Audio and Video files metadata using https://github.com/JamesHeinrich/getID3 lib
 * And extract video thumbnail using local ffmpeg.
 *
 * @see https://github.com/JamesHeinrich/getID3
 */
final class DocumentAudioVideoMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly DocumentFactory $documentFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?string $ffmpegPath,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
    }

    protected function supports(DocumentInterface $document): bool
    {
        /*
         * If none of AV tool are available, do not stream media for nothing.
         */
        return $document->isLocal()
            && ($document->isVideo() || $document->isAudio())
            && (\class_exists('getID3') || is_string($this->ffmpegPath));
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        /*
         * This process requires document files to be locally stored!
         */
        $videoPath = \tempnam(\sys_get_temp_dir(), 'video_');
        if (false === $videoPath) {
            throw new UnrecoverableMessageHandlingException('Unable to create temporary file for video processing.');
        }
        \rename($videoPath, $videoPath .= $document->getFilename());

        /*
        * Copy AV locally
        */
        $videoPathResource = \fopen($videoPath, 'w');
        if (false === $videoPathResource) {
            throw new UnrecoverableMessageHandlingException('Unable to open temporary file for video processing.');
        }
        \stream_copy_to_stream($this->documentsStorage->readStream($document->getMountPath()), $videoPathResource);
        \fclose($videoPathResource);

        $this->extractMediaMetadata($document, $videoPath);
        $this->extractMediaThumbnail($document, $videoPath);

        /*
         * Then delete local AV file
         */
        \unlink($videoPath);
    }

    protected function extractMediaMetadata(DocumentInterface $document, string $localMediaPath): void
    {
        if (!\class_exists('getID3')) {
            return;
        }

        $id3 = new \getID3();
        $fileInfo = $id3->analyze($localMediaPath);

        if ($document instanceof SizeableInterface && isset($fileInfo['video'])) {
            if (isset($fileInfo['video']['resolution_x'])) {
                $document->setImageWidth($fileInfo['video']['resolution_x']);
            }
            if (isset($fileInfo['video']['resolution_y'])) {
                $document->setImageHeight($fileInfo['video']['resolution_y']);
            }
        }
        if ($document instanceof TimeableInterface && isset($fileInfo['playtime_seconds'])) {
            $document->setMediaDuration((int) floor($fileInfo['playtime_seconds']));
        }
    }

    protected function extractMediaThumbnail(DocumentInterface $document, string $localMediaPath): void
    {
        if (!$document->isVideo() || !is_string($this->ffmpegPath)) {
            return;
        }

        $thumbnailPath = \tempnam(\sys_get_temp_dir(), 'thumbnail_');
        if (false === $thumbnailPath) {
            throw new UnrecoverableMessageHandlingException('Unable to create temporary file for thumbnail processing.');
        }
        \rename($thumbnailPath, $thumbnailPath .= '.jpg');

        $process = new Process([$this->ffmpegPath, '-y', '-i', $localMediaPath, '-vframes', '1', $thumbnailPath]);

        try {
            $process->mustRun();
            $process->wait();

            $thumbnailDocument = $this->documentFactory
                ->setFolder($document->getFolders()->first() ?: null)
                ->setFile(new File($thumbnailPath))
                ->getDocument();
            if ($thumbnailDocument instanceof HasThumbnailInterface && $document instanceof HasThumbnailInterface) {
                $thumbnailDocument->setOriginal($document);
                $document->getThumbnails()->add($thumbnailDocument);
                $this->managerRegistry->getManager()->flush();
                $this->eventDispatcher->dispatch(new DocumentCreatedEvent($thumbnailDocument));
            }
        } catch (ProcessFailedException $exception) {
            throw new UnrecoverableMessageHandlingException(sprintf('Cannot extract thumbnail from %s video file : %s', $localMediaPath, $exception->getMessage()));
        }
    }
}
