<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class ExportController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private readonly array $csvEncoderOptions,
    ) {
    }

    /**
     * Export all Node in a CSV file.
     */
    #[Route(
        path: '/rz-admin/nodes/export/all-{translationId}.csv',
        name: 'nodesExportAllCsvPage',
        requirements: ['translationId' => '[0-9]+'],
        defaults: [
            'parentNodeId' => null,
        ],
    )]
    #[Route(
        path: '/rz-admin/nodes/export/node-{parentNodeId}-{translationId}.csv',
        name: 'nodesExportNodeCsvPage',
        requirements: [
            'translationId' => '[0-9]+',
            'parentNodeId' => '[0-9]+',
        ],
        defaults: [
            'parentNodeId' => null,
        ],
    )]
    public function exportAllAction(
        #[MapEntity(
            expr: 'repository.find(translationId)',
            message: 'Translation does not exist'
        )]
        Translation $translation,
        #[MapEntity(
            expr: 'parentNodeId ? repository.find(parentNodeId) : null',
            message: 'Node does not exist'
        )]
        ?Node $parentNode = null,
    ): Response {
        $criteria = ['translation' => $translation];
        $order = ['node.nodeTypeName' => 'ASC'];
        $filename = 'nodes-'.date('YmdHis').'.'.$translation->getLocale().'.csv';

        if (null !== $parentNode) {
            $this->denyAccessUnlessGranted(NodeVoter::READ, $parentNode);
            $criteria['node.parent'] = $parentNode;
            $filename = $parentNode->getNodeName().'-'.date('YmdHis').'.'.$translation->getLocale().'.csv';
        } else {
            $this->denyAccessUnlessGranted(NodeVoter::READ_AT_ROOT);
        }

        $sources = $this->allStatusesNodesSourcesRepository->findBy($criteria, $order);

        $response = new StreamedResponse(function () use ($sources) {
            echo $this->serializer->serialize($sources, 'csv', [
                ...$this->csvEncoderOptions,
                'groups' => [
                    'nodes_sources',
                    'urls',
                    'tag_base',
                    'document_display',
                ],
            ]);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            )
        );

        return $response;
    }
}
