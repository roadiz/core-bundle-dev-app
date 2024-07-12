<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Api\Dto\Archive;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Enable distinct archive compiling for current Api Resource based on a date-time field.
 * ```yaml
 * pagination_enabled: false
 * pagination_client_enabled: false
 * archive_enabled: true
 * archive_publication_field_name: publishedAt
 * ```
 *
 * ```
 * "hydra:member": [
 *      {
 *          "@type": "Archive",
 *          "year": 2022,
 *          "months": {
 *              "2022-06": "2022-06-01T00:00:00+02:00",
 *              "2022-05": "2022-05-01T00:00:00+02:00"
 *          }
 *      },
 *      {
 *          "@type": "Archive",
 *          "year": 2021,
 *          "months": {
 *              "2021-09": "2021-09-01T00:00:00+02:00"
 *          }
 *      }
 *  ],
 * ```
 */
final class ArchiveExtension implements QueryResultCollectionExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $defaultPublicationFieldName = 'publishedAt'
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!$this->supportsResult($resourceClass, $operation)) {
            return;
        }
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return;
        }
        $aliases = $queryBuilder->getRootAliases();
        $alias = reset($aliases);
        $publicationFieldName = $this->getPublicationFieldName($operation);
        $publicationField = $alias . '.' . $publicationFieldName;

        $queryBuilder->select($publicationField)
            ->addGroupBy($publicationField)
            ->orderBy($publicationField, 'DESC');
    }

    public function supportsResult(string $resourceClass, ?Operation $operation = null, array $context = []): bool
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return false;
        }

        return $this->isArchiveEnabled($operation);
    }

    public function getResult(
        QueryBuilder $queryBuilder,
        ?string $resourceClass = null,
        ?Operation $operation = null,
        array $context = []
    ): iterable {
        $entities = [];
        $dates = [];
        $paginator = new Paginator($queryBuilder, false);
        $paginator->setUseOutputWalkers(false);
        /*
         * disable pagination to get all archives
         */
        $paginator->getQuery()->setMaxResults(null);
        $paginator->getQuery()->setFirstResult(null);

        foreach ($paginator as $result) {
            $dateTimeField = reset($result);
            if ($dateTimeField instanceof \DateTimeInterface) {
                $year = $dateTimeField->format('Y');
                $month = $dateTimeField->format('Y-m');

                if (!isset($dates[$year])) {
                    $dates[$year] = [];
                }
                if (!isset($dates[$year][$month])) {
                    $dates[$year][$month] = new \DateTime($dateTimeField->format('Y-m-01'));
                }
            }
        }

        foreach ($dates as $year => $months) {
            $entity = new Archive();
            $entity->year = $year;
            $entity->months = $months;
            $entities[] = $entity;
        }

        return $entities;
    }

    private function isArchiveEnabled(
        ?Operation $operation = null
    ): bool {
        return $operation->getExtraProperties()['archive_enabled'] ?? false;
    }

    private function getPublicationFieldName(
        ?Operation $operation = null
    ): string {
        return $operation->getExtraProperties()['archive_publication_field_name'] ?? $this->defaultPublicationFieldName;
    }
}
