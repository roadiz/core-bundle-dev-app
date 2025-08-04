<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

final class AjaxDocumentsExplorerController extends AbstractAjaxExplorerController
{
    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $arrayFilter = [
            // Prevent raw documents to show in explorer.
            'raw' => false,
            // Prevent thumbnail documents to show in explorer.
            'original' => null,
        ];

        if ($request->query->has('folderId') && $request->get('folderId') > 0) {
            $folder = $this->managerRegistry
                ->getRepository(Folder::class)
                ->find($request->get('folderId'));

            $arrayFilter['folders'] = [$folder];
        }
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Document::class,
            $arrayFilter,
            [
                'createdAt' => 'DESC',
            ]
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        // Use a factor of 12 for a better grid display
        $listManager->setItemPerPage(36);
        $listManager->handle();

        $documents = $listManager->getEntities();
        $documentsArray = $this->normalizeDocuments($documents);

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'documents' => $documentsArray,
            'documentsCount' => count($documents),
            'filters' => $listManager->getAssignation(),
            'trans' => $this->getTrans(),
        ];

        if ($request->query->has('folderId') && $request->get('folderId') > 0) {
            $responseArray['filters'] = array_merge($responseArray['filters'], [
                'folderId' => $request->get('folderId'),
            ]);
        }

        return $this->createSerializedResponse(
            $responseArray
        );
    }

    /**
     * Get a Document list from an array of id.
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }
        $cleanDocumentIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $documentsArray = [];

        if (count($cleanDocumentIds)) {
            $documents = $this->managerRegistry->getRepository(Document::class)->findBy([
                'id' => $cleanDocumentIds,
                'raw' => false,
            ]);
            // Sort array by ids given in request
            $documents = $this->sortIsh($documents, $cleanDocumentIds);
            $documentsArray = $this->normalizeDocuments($documents);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'documents' => $documentsArray,
            'trans' => $this->getTrans(),
        ]);
    }

    /**
     * Normalize response Document list result.
     *
     * @param iterable<Document> $documents
     */
    private function normalizeDocuments(iterable $documents): array
    {
        $documentsArray = [];

        foreach ($documents as $doc) {
            $documentModel = $this->explorerItemFactory->createForEntity($doc);
            $documentsArray[] = $documentModel->toArray();
        }

        return $documentsArray;
    }

    /**
     * Get an array of translations.
     */
    private function getTrans(): array
    {
        return [
            'editDocument' => $this->translator->trans('edit.document'),
            'unlinkDocument' => $this->translator->trans('unlink.document'),
            'linkDocument' => $this->translator->trans('link.document'),
            'moreItems' => $this->translator->trans('more.documents'),
        ];
    }
}
