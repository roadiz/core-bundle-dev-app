<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

abstract class AbstractDocumentFinder implements DocumentFinderInterface
{
    /**
     * @inheritDoc
     */
    public function findVideosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = [
            $basename . '.ogg',
            $basename . '.ogv',
            $basename . '.mp4',
            $basename . '.mov',
            $basename . '.avi',
            $basename . '.webm',
            $basename . '.mkv',
        ];

        return $this->findAllByFilenames($sourcesDocsName);
    }

    /**
     * @inheritDoc
     */
    public function findAudiosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = [
            $basename . '.mp3',
            $basename . '.ogg',
            $basename . '.wav',
            $basename . '.m4a',
            $basename . '.aac',
        ];

        return $this->findAllByFilenames($sourcesDocsName);
    }

    /**
     * @inheritDoc
     */
    public function findPicturesWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = [
            $basename . '.jpg',
            $basename . '.gif',
            $basename . '.png',
            $basename . '.jpeg',
            $basename . '.webp',
        ];

        return $this->findAllByFilenames($sourcesDocsName);
    }
}
