<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

abstract class AbstractDocumentFinder implements DocumentFinderInterface
{
    public function findVideosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = [
            $basename.'.ogg',
            $basename.'.ogv',
            $basename.'.mp4',
            $basename.'.mov',
            $basename.'.avi',
            $basename.'.webm',
            $basename.'.mkv',
        ];

        return $this->findAllByFilenames($sourcesDocsName);
    }

    public function findAudiosWithFilename(string $fileName): iterable
    {
        $basename = pathinfo($fileName);
        $basename = $basename['filename'];

        $sourcesDocsName = [
            $basename.'.mp3',
            $basename.'.ogg',
            $basename.'.wav',
            $basename.'.m4a',
            $basename.'.aac',
        ];

        return $this->findAllByFilenames($sourcesDocsName);
    }

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
        $sourcesDocsName = array_values(array_map(function ($extension) use ($basename) {
            return $basename.'.'.$extension;
        }, $extensionsList));

        return $this->findAllByFilenames($sourcesDocsName);
    }
}
