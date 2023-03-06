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
     * @param string $fileName
     *
     * @return iterable<DocumentInterface>
     */
    public function findVideosWithFilename(string $fileName): iterable;

    /**
     * @param string $fileName
     *
     * @return iterable<DocumentInterface>
     */
    public function findAudiosWithFilename(string $fileName): iterable;

    /**
     * @param string $fileName
     *
     * @return iterable<DocumentInterface>
     */
    public function findPicturesWithFilename(string $fileName): iterable;

    /**
     * @param array<string> $fileNames
     *
     * @return DocumentInterface|null
     */
    public function findOneByFilenames(array $fileNames): ?DocumentInterface;

    /**
     * @param string $hash
     * @param string $algorithm
     * @return DocumentInterface|null
     */
    public function findOneByHashAndAlgorithm(string $hash, string $algorithm): ?DocumentInterface;
}
