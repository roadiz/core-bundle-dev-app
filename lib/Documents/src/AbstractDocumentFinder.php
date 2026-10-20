<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

abstract class AbstractDocumentFinder implements DocumentFinderInterface
{
    /**
     * Extensions of a video alternative sources.
     */
    public const array VIDEO_SOURCE_EXTENSIONS = ['ogg', 'ogv', 'mp4', 'mov', 'avi', 'webm', 'mkv'];

    /**
     * Extensions of an audio alternative sources.
     */
    public const array AUDIO_SOURCE_EXTENSIONS = ['mp3', 'ogg', 'wav', 'm4a', 'aac'];

    #[\Override]
    public function findVideosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = array_map(fn (string $extension) => $basename.'.'.$extension, self::VIDEO_SOURCE_EXTENSIONS);

        return $this->findAllByFilenames($sourcesDocsName);
    }

    #[\Override]
    public function findAudiosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = array_map(fn (string $extension) => $basename.'.'.$extension, self::AUDIO_SOURCE_EXTENSIONS);

        return $this->findAllByFilenames($sourcesDocsName);
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
