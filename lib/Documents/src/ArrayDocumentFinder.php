<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * DocumentFinder for testing purposes only.
 */
final class ArrayDocumentFinder extends AbstractDocumentFinder
{
    /**
     * @var ArrayCollection<int, DocumentInterface>
     */
    private ArrayCollection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @param array<string> $fileNames
     * @return ArrayCollection<int, DocumentInterface>
     */
    public function findAllByFilenames(array $fileNames): ArrayCollection
    {
        return $this->documents->filter(
            function (DocumentInterface $document) use ($fileNames) {
                return in_array($document->getFilename(), $fileNames);
            }
        );
    }

    public function findOneByFilenames(array $fileNames): ?DocumentInterface
    {
        return $this->documents->filter(
            function (DocumentInterface $document) use ($fileNames) {
                return in_array($document->getFilename(), $fileNames);
            }
        )->first() ?: null;
    }

    public function findOneByHashAndAlgorithm(string $hash, string $algorithm): ?DocumentInterface
    {
        return null;
    }


    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function addDocument(DocumentInterface $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
        return $this;
    }
}
