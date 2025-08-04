<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class ExportController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
        private readonly TranslationRepository $translationRepository,
        private readonly array $csvEncoderOptions,
    ) {
    }

    /**
     * Export all Node in a CSV file.
     */
    public function exportAllAction(int $translationId, ?int $parentNodeId = null): Response
    {
        $translation = $this->translationRepository->find($translationId);

        if (null === $translation) {
            $translation = $this->translationRepository->findDefault();
        }
        $criteria = ['translation' => $translation];
        $order = ['node.nodeTypeName' => 'ASC'];
        $filename = 'nodes-'.date('YmdHis').'.'.$translation->getLocale().'.csv';

        if (null !== $parentNodeId) {
            /** @var Node|null $parentNode */
            $parentNode = $this->allStatusesNodeRepository->find($parentNodeId);
            if (null === $parentNode) {
                throw $this->createNotFoundException();
            }
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
