<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Doctrine\Common\Collections\ArrayCollection;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * DocumentFinder for testing purposes only.
 */
final class ArrayDocumentFinder extends AbstractDocumentFinder
{
    /**
     * @var ArrayCollection<int, DocumentInterface>
     */
    private readonly ArrayCollection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @param array<string> $fileNames
     *
     * @return ArrayCollection<int, DocumentInterface>
     */
    #[\Override]
    public function findAllByFilenames(array $fileNames): ArrayCollection
    {
        return $this->documents->filter(
            fn (DocumentInterface $document) => in_array($document->getFilename(), $fileNames)
        );
    }

    #[\Override]
    public function findOneByFilenames(array $fileNames): ?DocumentInterface
    {
        return $this->documents->filter(
            fn (DocumentInterface $document) => in_array($document->getFilename(), $fileNames)
        )->first() ?: null;
    }

    #[\Override]
    public function findOneByHashAndAlgorithm(string $hash, string $algorithm): ?DocumentInterface
    {
        return null;
    }

    /**
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
