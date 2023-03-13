<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;

/**
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class GlobalNodeSourceSearchHandler
{
    private ObjectManager $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityRepository<NodesSources>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(NodesSources::class);
    }

    /**
     * @param bool $displayNonPublishedNodes
     *
     * @return $this
     */
    public function setDisplayNonPublishedNodes(bool $displayNonPublishedNodes): self
    {
        $this->getRepository()->setDisplayingNotPublishedNodes($displayNonPublishedNodes);
        return $this;
    }

    /**
     * @param string $searchTerm
     * @param int $resultCount
     * @param Translation|null $translation
     * @return NodesSources[]
     */
    public function getNodeSourcesBySearchTerm(
        string $searchTerm,
        int $resultCount,
        ?Translation $translation = null
    ): array {
        $safeSearchTerms = strip_tags($searchTerm);

        /*
         * First try with Solr
         */
        /** @var array $nodesSources */
        $nodesSources = $this->getRepository()->findBySearchQuery(
            $safeSearchTerms,
            $resultCount
        );

        /*
         * Second try with sources fields
         */
        if (count($nodesSources) === 0) {
            $nodesSources = $this->getRepository()->searchBy(
                $safeSearchTerms,
                [],
                [],
                $resultCount
            );

            if (count($nodesSources) === 0) {
                /*
                 * Then try with node name.
                 */
                $qb = $this->getRepository()->createQueryBuilder('ns');

                $qb->select('ns, n')
                    ->innerJoin('ns.node', 'n')
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('n.nodeName', ':nodeName'),
                        $qb->expr()->like('ns.title', ':nodeName')
                    ))
                    ->setMaxResults($resultCount)
                    ->setParameter('nodeName', '%' . $safeSearchTerms . '%');

                if (null !== $translation) {
                    $qb->andWhere($qb->expr()->eq('ns.translation', ':translation'))
                        ->setParameter('translation', $translation);
                }
                try {
                    return $qb->getQuery()->getResult();
                } catch (NoResultException $e) {
                    return [];
                }
            }
        }

        return $nodesSources;
    }
}
