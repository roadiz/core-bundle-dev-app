<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use PhpOffice\PhpSpreadsheet\Writer\Exception;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Xlsx\NodeSourceXlsxSerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Themes\Rozier\RozierApp;

class ExportController extends RozierApp
{
    public function __construct(private readonly NodeSourceXlsxSerializer $xlsxSerializer)
    {
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
     * @throws Exception
     */
    public function exportAllXlsxAction(Request $request, int $translationId, ?int $parentNodeId = null): Response
    {
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
            $this->denyAccessUnlessGranted(NodeVoter::READ, $parentNode);
            $criteria['node.parent'] = $parentNode;
            $filename = $parentNode->getNodeName() . '-' . date("YmdHis") . '.' . $translation->getLocale() . '.xlsx';
        } else {
            $this->denyAccessUnlessGranted(NodeVoter::READ_AT_ROOT);
        }

        $sources = $this->em()
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findBy($criteria, $order);

        $this->xlsxSerializer->setOnlyTexts(true);
        $this->xlsxSerializer->addUrls();
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
