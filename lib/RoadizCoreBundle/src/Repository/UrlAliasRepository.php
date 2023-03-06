<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method UrlAlias|null findOneByName(string $name)
 * @method UrlAlias|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlAlias|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlAlias[]    findAll()
 * @method UrlAlias[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<UrlAlias>
 */
final class UrlAliasRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, UrlAlias::class, $dispatcher);
    }

    /**
     * Get all url aliases linked to given node.
     *
     * @param integer $nodeId
     *
     * @return array
     */
    public function findAllFromNode($nodeId)
    {
        $query = $this->_em->createQuery('
            SELECT ua FROM RZ\Roadiz\CoreBundle\Entity\UrlAlias ua
            INNER JOIN ua.nodeSource ns
            INNER JOIN ns.node n
            WHERE n.id = :nodeId')
                        ->setParameter('nodeId', (int) $nodeId);

        return $query->getResult();
    }

    /**
     * @param string $alias
     *
     * @return boolean
     */
    public function exists($alias)
    {
        $query = $this->_em->createQuery('
            SELECT COUNT(ua.alias) FROM RZ\Roadiz\CoreBundle\Entity\UrlAlias ua
            WHERE ua.alias = :alias')
                        ->setParameter('alias', $alias);

        return (bool) $query->getSingleScalarResult();
    }
}
