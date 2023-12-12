<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

abstract class GeneratedEntityFilter extends AbstractFilter
{
    private string $generatedEntityNamespacePattern;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface|null $logger
     * @param array|null $properties
     * @param NameConverterInterface|null $nameConverter
     * @param string $generatedEntityNamespacePattern
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger = null,
        array $properties = null,
        NameConverterInterface $nameConverter = null,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);

        $this->generatedEntityNamespacePattern = $generatedEntityNamespacePattern;
    }

    /**
     * @return string
     */
    public function getGeneratedEntityNamespacePattern(): string
    {
        return $this->generatedEntityNamespacePattern;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
