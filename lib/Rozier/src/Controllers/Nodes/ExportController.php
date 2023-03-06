<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Xlsx\NodeSourceXlsxSerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Themes\Rozier\RozierApp;

class ExportController extends RozierApp
{
    private NodeSourceXlsxSerializer $xlsxSerializer;

    /**
     * @param NodeSourceXlsxSerializer $xlsxSerializer
     */
    public function __construct(NodeSourceXlsxSerializer $xlsxSerializer)
    {
        $this->xlsxSerializer = $xlsxSerializer;
    }

    /**
     * Export all Node in a XLSX file (Excel).
     *
     * @param Request $request
     * @param int $translationId
     * @param int|null $parentNodeId
     *
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportAllXlsxAction(Request $request, int $translationId, ?int $parentNodeId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        /*
         * Get translation
         */
        $translation = $this->em()
            ->find(Translation::class, $translationId);

        if (null === $translation) {
            $translation = $this->em()
                ->getRepository(Translation::class)
                ->findDefault();
        }
        $criteria = ["translation" => $translation];
        $order = ['node.nodeType' => 'ASC'];
        $filename = 'nodes-' . date("YmdHis") . '.' . $translation->getLocale() . '.xlsx';

        if (null !== $parentNodeId) {
            /** @var Node|null $parentNode */
            $parentNode = $this->em()->find(Node::class, $parentNodeId);
            if (null === $parentNode) {
                throw $this->createNotFoundException();
            }
            $criteria['node.parent'] = $parentNode;
            $filename = $parentNode->getNodeName() . '-' . date("YmdHis") . '.' . $translation->getLocale() . '.xlsx';
        }

        $sources = $this->em()
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findBy($criteria, $order);

        $this->xlsxSerializer->setOnlyTexts(true);
        $this->xlsxSerializer->addUrls($request, $this->getSettingsBag()->get('force_locale'));
        $xlsx = $this->xlsxSerializer->serialize($sources);

        $response = new Response(
            $xlsx,
            Response::HTTP_OK,
            []
        );

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            )
        );

        $response->prepare($request);

        return $response;
    }
}
