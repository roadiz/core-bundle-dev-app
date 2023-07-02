<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Exception\FilterValidationException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class LocaleFilter extends GeneratedEntityFilter
{
    public const PROPERTY = '_locale';

    private PreviewResolverInterface $previewResolver;

    public function __construct(
        PreviewResolverInterface $previewResolver,
        ManagerRegistry $managerRegistry,
        ?RequestStack $requestStack = null,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
        LoggerInterface $logger = null,
        array $properties = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $generatedEntityNamespacePattern, $logger, $properties);
        $this->previewResolver = $previewResolver;
    }


    /**
     * Passes a property through the filter.
     *
     * @param string $property
     * @param mixed $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     * @throws \Exception
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if ($property !== self::PROPERTY) {
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

        if (count($supportedLocales) === 0) {
            throw new FilterValidationException(
                ['Locale filter is not available because no translation exist.']
            );
        }

        if (!in_array($value, $supportedLocales)) {
            throw new FilterValidationException(
                [sprintf(
                    'Locale filter value "%s" not supported. Supported values are %s',
                    $value,
                    implode(', ', $supportedLocales)
                )]
            );
        }

        /*
         * Apply translation filter only for NodesSources
         */
        if (
            $resourceClass === NodesSources::class ||
            preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0
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
                throw new FilterValidationException(['No translation exist for locale: ' . $value]);
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
     *
     * @param string $resourceClass
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $supportedLocales = $this->managerRegistry->getRepository(Translation::class)->getAvailableLocales();
        return  [
            self::PROPERTY =>  [
                'property' => self::PROPERTY,
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'Filter items with translation locale (' . implode(', ', $supportedLocales) . ').'
                ]
            ]
        ];
    }
}
