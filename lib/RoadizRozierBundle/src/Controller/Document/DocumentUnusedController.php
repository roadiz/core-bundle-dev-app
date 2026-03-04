<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DocumentUnusedController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * See unused documents.
     */
    public function unusedAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $assignation['orphans'] = true;
        /** @var DocumentRepository $documentRepository */
        $documentRepository = $this->managerRegistry->getRepository(Document::class);

        $listManager = new QueryBuilderListManager(
            $request,
            $documentRepository->getAllUnusedQueryBuilder(),
            'd'
        );
        $sessionListFilter = new SessionListFilters('unused_documents_item_per_page', 50);
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $assignation['filters'] = $listManager->getAssignation();
        $assignation['documents'] = $listManager->getEntities();
        $assignation['thumbnailFormat'] = [
            'quality' => 50,
            'crop' => '1:1',
            'width' => 128,
            'sharpen' => 5,
            'inline' => false,
            'picture' => true,
            'controls' => false,
            'loading' => 'lazy',
        ];
        $assignation['pageTitle'] = 'unused_documents';
        $assignation['displayButtonAction'] = false;

        return $this->render('@RoadizRozier/documents/unused.html.twig', $assignation);
    }
}
