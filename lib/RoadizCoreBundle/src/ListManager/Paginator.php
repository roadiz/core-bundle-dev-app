<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * A simple paginator class to filter entities with limit and search.
 *
 * @template T of PersistableInterface
 */
#[Exclude]
class Paginator
{
    protected ?int $itemCount = null;
    protected ?string $searchPattern = null;
    protected ?int $totalCount = null;
    protected bool $displayNotPublishedNodes;
    protected bool $displayAllNodesStatuses;

    /**
     * @param class-string<T> $entityName
     */
    public function __construct(
        protected readonly ObjectManager $em,
        protected readonly string $entityName,
        protected int $itemsPerPage = 10,
        protected readonly array $criteria = [],
    ) {
        $this->displayNotPublishedNodes = false;
        $this->displayAllNodesStatuses = false;

        if (empty($this->entityName)) {
            throw new \RuntimeException('Entity name could not be empty', 1);
        }
        if ($this->itemsPerPage < 1) {
            throw new \RuntimeException('Items par page could not be lesser than 1.', 1);
        }
    }

    public function isDisplayingNotPublishedNodes(): bool
    {
        return $this->displayNotPublishedNodes;
    }

    public function setDisplayingNotPublishedNodes(bool $displayNonPublishedNodes): Paginator
    {
        $this->displayNotPublishedNodes = $displayNonPublishedNodes;

        return $this;
    }

    public function isDisplayingAllNodesStatuses(): bool
    {
        return $this->displayAllNodesStatuses;
    }

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     *
     * @return $this
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): Paginator
    {
        $this->displayAllNodesStatuses = $displayAllNodesStatuses;

        return $this;
    }

    public function getSearchPattern(): ?string
    {
        return $this->searchPattern;
    }

    /**
     * @return $this
     */
    public function setSearchPattern(?string $searchPattern): Paginator
    {
        $this->searchPattern = $searchPattern;

        return $this;
    }

    /**
     * Return total entities count for given criteria.
     */
    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            $repository = $this->getRepository();
            if ($repository instanceof EntityRepository) {
                if (null !== $this->searchPattern) {
                    $this->totalCount = $repository->countSearchBy($this->searchPattern, $this->criteria);
                } else {
                    $this->totalCount = $repository->countBy($this->criteria);
                }
            } else {
                if (null !== $this->searchPattern) {
                    /*
                     * Use QueryBuilder for non-roadiz entities
                     */
                    $alias = 'o';
                    $qb = $this->getSearchQueryBuilder($alias);
                    $qb->select($qb->expr()->countDistinct($alias));
                    try {
                        return (int) $qb->getQuery()->getSingleScalarResult();
                    } catch (NoResultException|NonUniqueResultException $e) {
                        return 0;
                    }
                }
                $this->totalCount = $repository->count($this->criteria);
            }
        }

        return $this->totalCount;
    }

    /**
     * Return page count according to criteria.
     */
    public function getPageCount(): int
    {
        return (int) ceil($this->getTotalCount() / $this->getItemsPerPage());
    }

    /**
     * Return entities filtered for current page.
     *
     * @return array<T>
     */
    public function findByAtPage(array $order = [], int $page = 1): array
    {
        if (null !== $this->searchPattern) {
            return $this->searchByAtPage($order, $page);
        } else {
            return $this->getRepository()
                ->findBy(
                    $this->criteria,
                    $order,
                    $this->getItemsPerPage(),
                    $this->getItemsPerPage() * ($page - 1)
                );
        }
    }

    /**
     * Use a search query to paginate instead of a findBy.
     *
     * @return array<T>
     *
     * @throws \Exception
     */
    public function searchByAtPage(array $order = [], int $page = 1): array
    {
        $repository = $this->getRepository();
        if ($repository instanceof EntityRepository) {
            // @phpstan-ignore-next-line
            return $repository->searchBy(
                $this->searchPattern,
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }

        /*
         * Use QueryBuilder for non-roadiz entities
         */
        $alias = 'o';
        $qb = $this->getSearchQueryBuilder($alias);
        $qb->setMaxResults($this->getItemsPerPage())
            ->setFirstResult($this->getItemsPerPage() * ($page - 1));

        foreach ($order as $key => $value) {
            $qb->addOrderBy($alias.'.'.$key, $value);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return $this
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    protected function getSearchQueryBuilder(string $alias): QueryBuilder
    {
        $searchableFields = $this->getSearchableFields();
        if (0 === count($searchableFields)) {
            throw new \RuntimeException('Entity has no searchable field.');
        }
        $qb = $this->getRepository()->createQueryBuilder($alias);
        $orX = [];
        foreach ($this->getSearchableFields() as $field) {
            $orX[] = $qb->expr()->like(
                'LOWER('.$alias.'.'.$field.')',
                $qb->expr()->literal('%'.\mb_strtolower($this->searchPattern).'%')
            );
        }
        $qb->andWhere($qb->expr()->orX(...$orX));

        return $qb;
    }

    protected function getSearchableFields(): array
    {
        $metadata = $this->em->getClassMetadata($this->entityName);
        if (!($metadata instanceof ClassMetadataInfo)) {
            throw new \RuntimeException('Entity has no metadata.');
        }

        return EntityRepository::getSearchableColumnsNames($metadata);
    }

    /**
     * @return EntityRepository<T>
     */
    protected function getRepository(): \Doctrine\ORM\EntityRepository
    {
        $repository = $this->em->getRepository($this->entityName);
        if ($repository instanceof StatusAwareRepository) {
            $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
            $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
        }

        return $repository;
    }
}
