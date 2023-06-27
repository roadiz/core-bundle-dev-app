<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class QueryBuilderListManager extends AbstractEntityListManager
{
    protected QueryBuilder $queryBuilder;
    protected ?Paginator $paginator = null;
    protected string $identifier;
    protected bool $debug = false;

    /**
     * @param Request|null $request
     * @param QueryBuilder $queryBuilder
     * @param string $identifier
     * @param bool $debug
     */
    public function __construct(
        ?Request $request,
        QueryBuilder $queryBuilder,
        string $identifier = 'obj',
        bool $debug = false
    ) {
        parent::__construct($request);
        $this->queryBuilder = $queryBuilder;
        $this->identifier = $identifier;
        $this->debug = $debug;
    }

    /**
     * @param string $search
     */
    protected function handleSearchParam(string $search): void
    {
        // Implement your custom logic
    }

    public function handle(bool $disabled = false)
    {
        $this->handleRequestQuery($disabled);
    }

    protected function handleOrderingParam(string $field, string $ordering): void
    {
        $this->queryBuilder->addOrderBy(
            sprintf('%s.%s', $this->identifier, $field),
            $ordering
        );
    }

    /**
     * @return Paginator
     */
    protected function getPaginator(): Paginator
    {
        if (null === $this->paginator) {
            $this->paginator = new Paginator($this->queryBuilder);
        }
        return $this->paginator;
    }

    /**
     * @inheritDoc
     */
    public function setPage(int $page): self
    {
        parent::setPage($page);
        $this->queryBuilder->setFirstResult($this->getItemPerPage() * ($page - 1));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setItemPerPage(int $itemPerPage): self
    {
        parent::setItemPerPage($itemPerPage);
        $this->queryBuilder->setMaxResults((int) $itemPerPage);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getItemCount(): int
    {
        return $this->getPaginator()->count();
    }

    /**
     * @inheritDoc
     */
    public function getEntities(): Paginator
    {
        return $this->getPaginator();
    }

    /**
     * @return array
     */
    public function getAssignation(): array
    {
        if ($this->debug) {
            return array_merge(parent::getAssignation(), [
                'dql_query' => $this->queryBuilder->getDQL()
            ]);
        }
        return parent::getAssignation();
    }
}
