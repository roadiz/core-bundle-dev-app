<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class LocaleFilter extends GeneratedEntityFilter
{
    public const PROPERTY = '_locale';

    public function __construct(
        private readonly PreviewResolverInterface $previewResolver,
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter, $generatedEntityNamespacePattern);
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (self::PROPERTY !== $property) {
            return;
        }

        if ($this->previewResolver->isPreview()) {
            $supportedLocales = $this->managerRegistry
                ->getRepository(Translation::class)
                ->getAllLocales();
        } else {
            $supportedLocales = $this->managerRegistry
                ->getRepository(Translation::class)
                ->getAvailableLocales();
        }

        if (0 === count($supportedLocales)) {
            throw new InvalidArgumentException('Locale filter is not available because no translation exist.');
        }

        if (!in_array($value, $supportedLocales)) {
            throw new InvalidArgumentException(sprintf('Locale filter value "%s" not supported. Supported values are %s', $value, implode(', ', $supportedLocales)));
        }

        /*
         * Apply translation filter only for NodesSources
         */
        if (
            NodesSources::class === $resourceClass
            || preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0
        ) {
            if ($this->previewResolver->isPreview()) {
                $translation = $this->managerRegistry
                    ->getRepository(Translation::class)
                    ->findOneByLocaleOrOverrideLocale($value);
            } else {
                $translation = $this->managerRegistry
                    ->getRepository(Translation::class)
                    ->findOneAvailableByLocaleOrOverrideLocale($value);
            }

            if (null === $translation) {
                throw new InvalidArgumentException('No translation exist for locale: '.$value);
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('o.translation', ':translation'))
                ->setParameter('translation', $translation);
        }
    }

    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - swagger (optional): additional parameters for the path operation, e.g. 'swagger' => ['description' => 'My Description']
     * The description can contain additional data specific to a filter.
     */
    public function getDescription(string $resourceClass): array
    {
        $supportedLocales = $this->managerRegistry->getRepository(Translation::class)->getAvailableLocales();

        return [
            self::PROPERTY => [
                'property' => self::PROPERTY,
                'type' => 'string',
                'required' => false,
                'openapi' => new Parameter(
                    name: self::PROPERTY,
                    in: 'query',
                    description: 'Filter items with translation locale ('.implode(', ', $supportedLocales).').'
                ),
            ],
        ];
    }
}
