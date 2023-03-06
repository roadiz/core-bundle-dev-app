<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\RozierBundle\ListManager\SessionListFilters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

final class DocumentUnusedController extends RozierApp
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * See unused documents.
     *
     * @param  Request $request
     * @return Response
     */
    public function unusedAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $this->assignation['orphans'] = true;
        /** @var DocumentRepository $documentRepository */
        $documentRepository = $this->managerRegistry->getRepository(Document::class);

        $listManager = new QueryBuilderListManager(
            $request,
            $documentRepository->getAllUnusedQueryBuilder(),
            'd'
        );
        $listManager->setItemPerPage(static::DEFAULT_ITEM_PER_PAGE);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('unused_documents_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['documents'] = $listManager->getEntities();
        $this->assignation['thumbnailFormat'] = [
            'quality' => 50,
            'fit' => '128x128',
            'sharpen' => 5,
            'inline' => false,
            'picture' => true,
            'loading' => 'lazy',
        ];

        return $this->render('@RoadizRozier/documents/unused.html.twig', $this->assignation);
    }
}
