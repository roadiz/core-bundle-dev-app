<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

final class DocumentDuplicatesController extends RozierApp
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @param  Request $request
     * @return Response
     */
    public function duplicatedAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $this->assignation['duplicates'] = true;
        /** @var DocumentRepository $documentRepository */
        $documentRepository = $this->managerRegistry->getRepository(Document::class);

        $listManager = new QueryBuilderListManager(
            $request,
            $documentRepository->getDuplicatesQueryBuilder(),
            'd'
        );
        $listManager->setItemPerPage(static::DEFAULT_ITEM_PER_PAGE);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('duplicated_documents_item_per_page');
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
            'controls' => false,
            'loading' => 'lazy',
        ];

        return $this->render('@RoadizRozier/documents/duplicated.html.twig', $this->assignation);
    }
}
