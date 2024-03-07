<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\Models\DocumentModel;

class AjaxDocumentsExplorerController extends AbstractAjaxController
{
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EmbedFinderFactory $embedFinderFactory
    ) {
    }

    public static array $thumbnailArray = [
        "fit" => "40x40",
        "quality" => 50,
        "inline" => false,
    ];

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
            $folder = $this->em()
                        ->find(
                            Folder::class,
                            $request->get('folderId')
                        );

            $arrayFilter['folders'] = [$folder];
        }
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Document::class,
            $arrayFilter,
            [
                'createdAt' => 'DESC'
            ]
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(30);
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
                'folderId' => $request->get('folderId')
            ]);
        }

        return new JsonResponse(
            $responseArray
        );
    }

    /**
     * Get a Document list from an array of id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }
        $cleanDocumentIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY
        ]));
        $documentsArray = [];

        if (count($cleanDocumentIds)) {
            $em = $this->em();
            $documents = $em->getRepository(Document::class)->findBy([
                'id' => $cleanDocumentIds,
                'raw' => false,
            ]);
            // Sort array by ids given in request
            $documents = $this->sortIsh($documents, $cleanDocumentIds);
            $documentsArray = $this->normalizeDocuments($documents);
        }

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'documents' => $documentsArray,
            'trans' => $this->getTrans()
        ];

        return new JsonResponse(
            $responseArray
        );
    }

    /**
     * Normalize response Document list result.
     *
     * @param array<Document>|\Traversable<Document> $documents
     * @return array
     */
    private function normalizeDocuments($documents)
    {
        $documentsArray = [];

        /** @var Document $doc */
        foreach ($documents as $doc) {
            $documentModel = new DocumentModel(
                $doc,
                $this->renderer,
                $this->documentUrlGenerator,
                $this->urlGenerator,
                $this->embedFinderFactory
            );
            $documentsArray[] = $documentModel->toArray();
        }

        return $documentsArray;
    }

    /**
     * Get an array of translations.
     *
     * @return array
     */
    private function getTrans()
    {
        return [
            'editDocument' => $this->getTranslator()->trans('edit.document'),
            'unlinkDocument' => $this->getTranslator()->trans('unlink.document'),
            'linkDocument' => $this->getTranslator()->trans('link.document'),
            'moreItems' => $this->getTranslator()->trans('more.documents')
        ];
    }
}
