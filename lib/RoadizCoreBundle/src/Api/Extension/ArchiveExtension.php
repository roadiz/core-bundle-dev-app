<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Api\Dto\Archive;
use Symfony\Component\HttpFoundation\Request;
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
    private ResourceMetadataFactoryInterface $resourceMetadataFactory;
    private RequestStack $requestStack;
    private string $defaultPublicationFieldName;

    public function __construct(
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        RequestStack $requestStack,
        string $defaultPublicationFieldName = 'publishedAt'
    ) {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->requestStack = $requestStack;
        $this->defaultPublicationFieldName = $defaultPublicationFieldName;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (!$this->supportsResult($resourceClass, $operationName)) {
            return;
        }
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return;
        }
        $aliases = $queryBuilder->getRootAliases();
        $alias = reset($aliases);
        $publicationFieldName = $this->getPublicationFieldName($request, $this->resourceMetadataFactory->create($resourceClass), $operationName);
        $publicationField = $alias . '.' . $publicationFieldName;

        $queryBuilder->select($publicationField)
            ->addGroupBy($publicationField)
            ->orderBy($publicationField, 'DESC');
    }

    public function supportsResult(string $resourceClass, string $operationName = null): bool
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return false;
        }

        return $this->isArchiveEnabled($request, $this->resourceMetadataFactory->create($resourceClass), $operationName);
    }

    public function getResult(QueryBuilder $queryBuilder): iterable
    {
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
            if ($dateTimeField instanceof \DateTime) {
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
        Request $request,
        ResourceMetadata $resourceMetadata,
        string $operationName = null
    ): bool {
        return $resourceMetadata->getCollectionOperationAttribute(
            $operationName,
            'archive_enabled',
            false,
            true
        );
    }

    private function getPublicationFieldName(
        Request $request,
        ResourceMetadata $resourceMetadata,
        string $operationName = null
    ): string {
        return $resourceMetadata->getCollectionOperationAttribute(
            $operationName,
            'archive_publication_field_name',
            $this->defaultPublicationFieldName,
            true
        );
    }
}
