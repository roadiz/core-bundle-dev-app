<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

final class ChainImporter implements EntityImporterInterface
{
    /**
     * @param array<EntityImporterInterface> $importers
     */
    public function __construct(private array $importers = [])
    {
    }

    public function addImporter(EntityImporterInterface $entityImporter): self
    {
        $this->importers[] = $entityImporter;

        return $this;
    }

    public function supports(string $entityClass): bool
    {
        foreach ($this->importers as $importer) {
            if ($importer instanceof EntityImporterInterface && $importer->supports($entityClass)) {
                return true;
            }
        }

        return false;
    }

    public function import(string $serializedData): bool
    {
        throw new \RuntimeException('You cannot call import method on ChainImporter, but importWithType method');
    }

    public function importWithType(string $serializedData, string $entityClass): bool
    {
        foreach ($this->importers as $importer) {
            if ($importer instanceof EntityImporterInterface && $importer->supports($entityClass)) {
                return $importer->import($serializedData);
            }
        }

        return false;
    }
}
