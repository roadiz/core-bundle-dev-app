<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\LogRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Twig\Error\RuntimeError;

#[AsController]
final class HistoryController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
        private readonly LogRepository $logRepository,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function historyAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException();
        }
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
