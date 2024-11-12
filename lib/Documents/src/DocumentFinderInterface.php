<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use RZ\Roadiz\Documents\Models\DocumentInterface;

interface DocumentFinderInterface
{
    /**
     * @param array<string> $fileNames
     *
     * @return iterable<DocumentInterface>
     */
    public function findAllByFilenames(array $fileNames): iterable;

    /**
     * @return iterable<DocumentInterface>
     */
    public function findVideosWithFilename(string $fileName): iterable;

    /**
     * @return iterable<DocumentInterface>
     */
    public function findAudiosWithFilename(string $fileName): iterable;

    /**
     * @return iterable<DocumentInterface>
     */
    public function findPicturesWithFilename(string $fileName): iterable;

    /**
     * @param array<string> $fileNames
     */
    public function findOneByFilenames(array $fileNames): ?DocumentInterface;

    public function findOneByHashAndAlgorithm(string $hash, string $algorithm): ?DocumentInterface;
}
