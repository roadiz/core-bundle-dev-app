<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\EntityHandler\DocumentHandler;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Events\DocumentDeletedEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class DocumentController extends AbstractController
{
    public function __construct(
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly LoggerInterface $logger,
        private readonly DocumentFactory $documentFactory,
        private readonly TranslatorInterface $translator,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function deleteAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_DELETE');

        /** @var Document|null $document */
        $document = $this->managerRegistry->getRepository(Document::class)->find($documentId);

        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildDeleteForm($document);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['documentId'] == $document->getId()
        ) {
            try {
                $this->eventDispatcher->dispatch(
                    new DocumentDeletedEvent($document)
                );
                $this->managerRegistry->getManager()->remove($document);
                $this->managerRegistry->getManager()->flush();
                $msg = $this->translator->trans('document.%name%.deleted', [
                    '%name%' => (string) $document,
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $document);
            } catch (\Exception $e) {
                $msg = $this->translator->trans('document.%name%.cannot_delete', [
                    '%name%' => (string) $document,
                ]);
                $this->logger->error($e->getMessage());
                $this->logTrail->publishErrorMessage($request, $msg, $document);
            }

            return $this->redirectToRoute('documentsHomePage');
        }

        return $this->render('@RoadizRozier/documents/delete.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_DELETE');

        $documentsIds = $request->get('documents', []);

        if (is_string($documentsIds)) {
            $documentsIds = json_decode($documentsIds, true, flags: JSON_THROW_ON_ERROR);
        }

        if (!is_array($documentsIds) || count($documentsIds) <= 0) {
            throw new ResourceNotFoundException('No selected documents to delete.');
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

        $form = $this->buildBulkDeleteForm($documentsIds);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($documents as $document) {
                $this->managerRegistry->getManager()->remove($document);
                $msg = $this->translator->trans(
                    'document.%name%.deleted',
                    ['%name%' => (string) $document]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $document);
            }
            $this->managerRegistry->getManager()->flush();

            return $this->redirectToRoute('documentsHomePage');
        }

        return $this->render('@RoadizRozier/documents/bulkDelete.html.twig', [
            'documents' => $documents,
            'form' => $form->createView(),
            'action' => '?'.http_build_query(['documents' => $documentsIds]),
        ]);
    }

    /**
     * Download document file.
     *
     * @throws FilesystemException
     */
    public function downloadAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->managerRegistry
            ->getRepository(Document::class)
            ->find($documentId);

        if (null !== $document) {
            /** @var DocumentHandler $handler */
            $handler = $this->handlerFactory->getHandler($document);

            return $handler->getDownloadResponse();
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Download document file inline.
     *
     * @throws FilesystemException
     */
    public function downloadInlineAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->managerRegistry
            ->getRepository(Document::class)
            ->find($documentId);

        if (null !== $document) {
            /** @var DocumentHandler $handler */
            $handler = $this->handlerFactory->getHandler($document);

            return $handler->getDownloadResponse(false);
        }

        throw new ResourceNotFoundException();
    }

    public function uploadAction(Request $request, ?int $folderId = null, string $_format = 'html'): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = null;
        if (null !== $folderId && $folderId > 0) {
            $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);
        }

        $form = $this->buildUploadForm($folderId);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $document = $this->uploadDocument($form, $folderId);

                if (null !== $document) {
                    $msg = $this->translator->trans('document.%name%.uploaded', [
                        '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
                    ]);
                    $this->logTrail->publishConfirmMessage($request, $msg, $document);

                    // Event must be dispatched AFTER flush for async concurrency matters
                    $this->eventDispatcher->dispatch(
                        new DocumentCreatedEvent($document)
                    );

                    if ('json' === $_format || $request->isXmlHttpRequest()) {
                        $documentModel = $this->explorerItemFactory->createForEntity(
                            $document
                        );

                        return new JsonResponse([
                            'success' => true,
                            'document' => $documentModel->toArray(),
                        ], Response::HTTP_CREATED);
                    } else {
                        return $this->redirectToRoute('documentsHomePage', ['folderId' => $folderId]);
                    }
                } else {
                    $msg = $this->translator->trans('document.cannot_persist');
                    $this->logTrail->publishErrorMessage($request, $msg, $document);

                    if ('json' === $_format || $request->isXmlHttpRequest()) {
                        throw $this->createNotFoundException($msg);
                    } else {
                        return $this->redirectToRoute('documentsHomePage', ['folderId' => $folderId]);
                    }
                }
            } elseif ('json' === $_format || $request->isXmlHttpRequest()) {
                /*
                 * Bad form submitted
                 */
                $errorPerForm = [];
                /** @var Form $child */
                foreach ($form as $child) {
                    if ($child->isSubmitted() && !$child->isValid()) {
                        /** @var FormError $error */
                        foreach ($child->getErrors() as $error) {
                            $errorPerForm[$child->getName()][] = $this->translator->trans($error->getMessage());
                        }
                    }
                }

                return new JsonResponse(
                    [
                        'errors' => $errorPerForm,
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        return $this->render('@RoadizRozier/documents/upload.html.twig', [
            'form' => $form->createView(),
            'maxUploadSize' => UploadedFile::getMaxFilesize() / 1024 / 1024,
            'folder' => $folder,
        ]);
    }

    private function buildDeleteForm(Document $doc): FormInterface
    {
        $defaults = [
            'documentId' => $doc->getId(),
        ];
        $builder = $this->createFormBuilder($defaults)
            ->add('documentId', HiddenType::class, [
                'data' => $doc->getId(),
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);

        return $builder->getForm();
    }

    private function buildBulkDeleteForm(array $documentsIds): FormInterface
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

    private function buildUploadForm(?int $folderId = null): FormInterface
    {
        $builder = $this->createFormBuilder([], [
            'csrf_protection' => false,
        ])
            ->add('attachment', FileType::class, [
                'label' => 'choose.documents.to_upload',
                'constraints' => [
                    new File(),
                ],
            ]);

        if (
            null !== $folderId
            && $folderId > 0
        ) {
            $builder->add('folderId', HiddenType::class, [
                'data' => $folderId,
            ]);
        }

        return $builder->getForm();
    }

    /**
     * Handle upload form data to create a Document.
     *
     * @throws FilesystemException
     */
    private function uploadDocument(FormInterface $data, ?int $folderId = null): ?DocumentInterface
    {
        $folder = null;
        if (null !== $folderId && $folderId > 0) {
            /** @var Folder $folder */
            $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);
        }

        if (empty($data['attachment'])) {
            return null;
        }

        $uploadedFile = $data['attachment']->getData();

        $this->documentFactory->setFile($uploadedFile);
        $this->documentFactory->setFolder($folder);

        if (null === $document = $this->documentFactory->getDocument()) {
            return null;
        }

        $this->managerRegistry->getManager()->flush();

        return $document;
    }
}
