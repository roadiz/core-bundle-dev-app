<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentArchiver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\RozierApp;

final class DocumentArchiveController extends RozierApp
{
    private ManagerRegistry $managerRegistry;
    private TranslatorInterface $translator;
    private DocumentArchiver $documentArchiver;

    public function __construct(
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        DocumentArchiver $documentArchiver
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->translator = $translator;
        $this->documentArchiver = $documentArchiver;
    }

    /**
     * Return an deletion form for multiple docs.
     *
     * @param Request $request
     *
     * @return Response
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

        if (count($documents) > 0) {
            $this->assignation['documents'] = $documents;
            $form = $this->buildBulkDownloadForm($documentsIds);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    return $this->documentArchiver->archiveAndServe($documents, 'Documents archive');
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    $msg = $this->translator->trans('documents.cannot_download');
                    $this->publishErrorMessage($request, $msg);
                }

                return $this->redirectToRoute('documentsHomePage');
            }

            $this->assignation['form'] = $form->createView();
            $this->assignation['action'] = '?' . http_build_query(['documents' => $documentsIds]);
            $this->assignation['thumbnailFormat'] = [
                'quality' => 50,
                'fit' => '128x128',
                'sharpen' => 5,
                'inline' => false,
                'picture' => true,
                'loading' => 'lazy',
            ];

            return $this->render('@RoadizRozier/documents/bulkDownload.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }


    private function buildBulkDownloadForm(array $documentsIds): FormInterface
    {
        $defaults = [
            'checksum' => md5(serialize($documentsIds)),
        ];
        $builder = $this->createFormBuilder($defaults, [
            'action' => '?' . http_build_query(['documents' => $documentsIds]),
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
