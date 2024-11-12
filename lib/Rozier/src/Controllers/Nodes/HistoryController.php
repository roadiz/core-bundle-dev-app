<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class HistoryController extends RozierApp
{
    /**
     * @throws RuntimeError
     */
    public function historyAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException();
        }
        $this->denyAccessUnlessGranted(NodeVoter::READ_LOGS, $node);

        $qb = $this->em()
            ->getRepository(Log::class)
            ->getAllRelatedToNodeQueryBuilder($node);

        $listManager = new QueryBuilderListManager($request, $qb, 'obj');
        $listManager->setSearchingCallable(function (QueryBuilder $queryBuilder, string $search) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('obj.message', ':search'),
                $queryBuilder->expr()->like('obj.channel', ':search')
            ));
            $queryBuilder->setParameter('search', '%'.$search.'%');
        });
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('user_history_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

        $this->assignation['node'] = $node;
        $this->assignation['translation'] = $this->em()->getRepository(Translation::class)->findDefault();
        $this->assignation['entries'] = $listManager->getEntities();
        $this->assignation['filters'] = $listManager->getAssignation();

        return $this->render('@RoadizRozier/nodes/history.html.twig', $this->assignation);
    }
}
