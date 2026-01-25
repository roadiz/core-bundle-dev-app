<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Repository\LogRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\RuntimeError;

#[AsController]
final class HistoryController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly LogRepository $logRepository,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    #[Route(
        path: '/rz-admin/nodes/history/{nodeId}/{page}',
        name: 'nodesHistoryPage',
        requirements: [
            'nodeId' => '[0-9]+',
            'page' => '[0-9]+',
        ],
        defaults: [
            'page' => 1,
        ],
    )]
    public function historyAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::READ_LOGS, $node);

        $qb = $this->logRepository->getAllRelatedToNodeQueryBuilder($node);

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
        $sessionListFilter = new SessionListFilters('user_history_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

        return $this->render('@RoadizRozier/nodes/history.html.twig', [
            'node' => $node,
            'entries' => $listManager->getEntities(),
            'filters' => $listManager->getAssignation(),
            'translation' => $this->managerRegistry->getRepository(Translation::class)->findDefault(),
        ]);
    }
}
