<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\AbstractDocumentFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentFinder extends AbstractDocumentFinder
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @inheritDoc
     */
    public function findAllByFilenames(array $fileNames): iterable
    {
        return $this->getRepository()->findBy([
            "filename" => $fileNames,
            "raw" => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByFilenames(array $fileNames): ?DocumentInterface
    {
        return $this->getRepository()->findOneBy([
            "filename" => $fileNames,
            "raw" => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByHashAndAlgorithm(string $hash, string $algorithm): ?DocumentInterface
    {
        return $this->getRepository()->findOneBy([
            "fileHash" => $hash,
            "fileHashAlgorithm" => $algorithm,
        ]);
    }

    /**
     * @return DocumentRepository
     */
    protected function getRepository(): DocumentRepository
    {
        return $this->managerRegistry->getRepository(Document::class);
    }
}
