<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use RZ\Roadiz\Documents\Models\DocumentInterface;

abstract class AbstractDocumentFinder implements DocumentFinderInterface
{
    /**
     * Extensions of a video alternative sources, playable in an HTML <video> element.
     */
    public const array VIDEO_SOURCE_EXTENSIONS = ['ogg', 'ogv', 'mp4', 'm4v', 'mov', 'webm', 'mkv'];

    /**
     * Extensions of an audio alternative sources, playable in an HTML <audio> element.
     */
    public const array AUDIO_SOURCE_EXTENSIONS = ['mp3', 'ogg', 'oga', 'opus', 'wav', 'flac', 'm4a', 'aac', 'weba'];

    #[\Override]
    public function findVideosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = array_map(fn (string $extension) => $basename.'.'.$extension, self::VIDEO_SOURCE_EXTENSIONS);

        /*
         * Some extensions (ogg) are shared between audio and video files.
         */
        return array_values(array_filter(
            iterator_to_array($this->findAllByFilenames($sourcesDocsName), false),
            fn (DocumentInterface $document) => $document->isVideo()
        ));
    }

    #[\Override]
    public function findAudiosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = array_map(fn (string $extension) => $basename.'.'.$extension, self::AUDIO_SOURCE_EXTENSIONS);

        return array_values(array_filter(
            iterator_to_array($this->findAllByFilenames($sourcesDocsName), false),
            fn (DocumentInterface $document) => $document->isAudio()
        ));
    }

    #[\Override]
    public function findPicturesWithFilename(string $fileName): iterable
    {
        $pathInfo = pathinfo($fileName);
        $basename = $pathInfo['filename'];
        $currentExtension = $pathInfo['extension'] ?? null;
        if (null === $currentExtension) {
            return [];
        }
        $extensionsList = [
            'jpg',
            'gif',
            'png',
            'jpeg',
            'webp',
            'avif',
        ];

        // remove current extension from list
        $extensionsList = array_diff($extensionsList, [$currentExtension]);
        // list sources paths for extensions
        $sourcesDocsName = array_values(array_map(fn ($extension) => $basename.'.'.$extension, $extensionsList));

        return $this->findAllByFilenames($sourcesDocsName);
    }
}
