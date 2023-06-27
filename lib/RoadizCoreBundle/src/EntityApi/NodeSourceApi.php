<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

class NodeSourceApi extends AbstractApi
{
    /**
     * @var class-string
     */
    protected string $nodeSourceClassName = NodesSources::class;

    /**
     * @param array|null $criteria
     * @return class-string<NodesSources>
     */
    protected function getNodeSourceClassName(array $criteria = null): string
    {
        if (isset($criteria['node.nodeType']) && $criteria['node.nodeType'] instanceof NodeType) {
            $this->nodeSourceClassName = $criteria['node.nodeType']->getSourceEntityFullQualifiedClassName();
            unset($criteria['node.nodeType']);
        } elseif (
            isset($criteria['node.nodeType']) &&
            is_array($criteria['node.nodeType']) &&
            count($criteria['node.nodeType']) === 1 &&
            $criteria['node.nodeType'][0] instanceof NodeType
        ) {
            $this->nodeSourceClassName = $criteria['node.nodeType'][0]->getSourceEntityFullQualifiedClassName();
            unset($criteria['node.nodeType']);
        } else {
            $this->nodeSourceClassName = NodesSources::class;
        }

        return $this->nodeSourceClassName;
    }

    /**
     * @return NodesSourcesRepository
     */
    public function getRepository(): NodesSourcesRepository
    {
        // @phpstan-ignore-next-line
        return $this->managerRegistry->getRepository($this->nodeSourceClassName);
    }

    /**
     * @param array $criteria
     * @param array|null $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array|Paginator
     */
    public function getBy(
        array $criteria,
        array $order = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        $this->getNodeSourceClassName($criteria);

        return $this->getRepository()
                    ->findBy(
                        $criteria,
                        $order,
                        $limit,
                        $offset
                    );
    }

    /**
     * @param array $criteria
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countBy(array $criteria)
    {
        $this->getNodeSourceClassName($criteria);
        return $this->getRepository()
                    ->countBy(
                        $criteria
                    );
    }

    /**
     * @param array $criteria
     * @param array|null $order
     * @return null|NodesSources
     * @throws NonUniqueResultException
     */
    public function getOneBy(array $criteria, array $order = null)
    {
        $this->getNodeSourceClassName($criteria);
        return $this->getRepository()
                    ->findOneBy(
                        $criteria,
                        $order
                    );
    }

    /**
     * Search Nodes-Sources using LIKE condition on title,
     * meta-title, meta-keywords and meta-description.
     *
     * @param string $textQuery
     * @param int $limit
     * @param array $nodeTypes
     * @param bool $onlyVisible
     * @param array $additionalCriteria
     * @return array
     */
    public function searchBy(
        string $textQuery,
        int $limit = 0,
        array $nodeTypes = [],
        bool $onlyVisible = false,
        array $additionalCriteria = []
    ) {
        return $this->getRepository()
            ->findByTextQuery(
                $textQuery,
                $limit,
                $nodeTypes,
                $onlyVisible,
                $additionalCriteria
            );
    }
}
