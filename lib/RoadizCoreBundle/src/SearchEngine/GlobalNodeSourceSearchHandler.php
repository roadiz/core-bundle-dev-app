<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Doctrine\ORM\NoResultException;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

final readonly class GlobalNodeSourceSearchHandler
{
    public function __construct(
        private AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler = null,
    ) {
    }

    private function getRepository(): NodesSourcesRepository
    {
        return $this->allStatusesNodesSourcesRepository;
    }

    /**
     * @return array<NodesSources|object>
     */
    public function getNodeSourcesBySearchTerm(
        string $searchTerm,
        int $resultCount,
        ?Translation $translation = null,
    ): array {
        $safeSearchTerms = strip_tags($searchTerm);

        if (empty($safeSearchTerms)) {
            return [];
        }

        /**
         * First try with Search engine.
         */
        $nodesSources = [];
        $resultCount = $resultCount > 0 ? $resultCount : 999999;

        if (null !== $this->nodeSourceSearchHandler) {
            try {
                $this->nodeSourceSearchHandler->boostByUpdateDate();
                $arguments = [
                    'status' => ['<=', NodeStatus::PUBLISHED],
                ];

                $nodesSources = $this->nodeSourceSearchHandler->search($safeSearchTerms, $arguments, $resultCount)->getResultItems();
            } catch (SearchEngineServerException) {
            }
        }

        if (count($nodesSources) > 0) {
            return array_map(fn (SearchResultItemInterface $item) => $item->getItem(), $nodesSources);
        }

        /*
         * Second try with sources fields
         */
        $nodesSources = $this->getRepository()->searchBy(
            $safeSearchTerms,
            [],
            [],
            $resultCount
        );

        if (0 === count($nodesSources)) {
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
                ->setParameter('nodeName', '%'.$safeSearchTerms.'%');

            if (null !== $translation) {
                $qb->andWhere($qb->expr()->eq('ns.translation', ':translation'))
                    ->setParameter('translation', $translation);
            }
            try {
                return $qb->getQuery()->getResult();
            } catch (NoResultException) {
                return [];
            }
        }

        return $nodesSources;
    }
}
