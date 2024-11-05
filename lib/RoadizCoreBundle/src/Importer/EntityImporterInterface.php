<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

interface EntityImporterInterface
{
    public function supports(string $entityClass): bool;

    public function import(string $serializedData): bool;
}
