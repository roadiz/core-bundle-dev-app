<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\DocumentArchiver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DocumentArchiveController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly DocumentArchiver $documentArchiver,
        private readonly LoggerInterface $logger,
        private readonly LogTrail $logTrail,
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function bulkDownloadAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $documentsIds = $request->get('documents', []);
        if (!is_array($documentsIds) || count($documentsIds) <= 0) {
            throw new ResourceNotFoundException('No selected documents to download.');
        }

        /** @var array<Document> $documents */
        $documents = $this->managerRegistry
            ->getRepository(Document::class)
            ->findBy([
                'id' => $documentsIds,
            ]);

        if (0 === count($documents)) {
            throw new ResourceNotFoundException();
        }

        $assignation = [];
        $assignation['documents'] = $documents;
        $form = $this->buildBulkDownloadForm($documentsIds);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->documentArchiver->archiveAndServe($documents, 'Documents archive');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $msg = $this->translator->trans('documents.cannot_download');
                $this->logTrail->publishErrorMessage($request, $msg);
            }

            return $this->redirectToRoute('documentsHomePage');
        }

        $assignation['form'] = $form->createView();
        $assignation['action'] = '?'.http_build_query(['documents' => $documentsIds]);
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

        return $this->render('@RoadizRozier/documents/bulkDownload.html.twig', $assignation);
    }

    private function buildBulkDownloadForm(array $documentsIds): FormInterface
    {
        $defaults = [
            'checksum' => md5(serialize($documentsIds)),
        ];
        $builder = $this->createFormBuilder($defaults, [
            'action' => '?'.http_build_query(['documents' => $documentsIds]),
        ])
            ->add('checksum', HiddenType::class, [
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);

        return $builder->getForm();
    }
}
