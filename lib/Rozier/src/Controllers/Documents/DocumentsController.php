<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Documents;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\MediaFinder\SoundcloudEmbedFinder;
use RZ\Roadiz\CoreBundle\Document\MediaFinder\YoutubeEmbedFinder;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use RZ\Roadiz\CoreBundle\EntityHandler\DocumentHandler;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Events\DocumentDeletedEvent;
use RZ\Roadiz\Documents\Events\DocumentFileUpdatedEvent;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use RZ\Roadiz\Documents\Exceptions\APINeedsAuthentificationException;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderInterface;
use RZ\Roadiz\Documents\MediaFinders\RandomImageFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Themes\Rozier\Forms\DocumentEditType;
use Themes\Rozier\Forms\DocumentEmbedType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class DocumentsController extends RozierApp
{
    protected array $thumbnailFormat = [
        'quality' => 50,
        'fit' => '128x128',
        'sharpen' => 5,
        'inline' => false,
        'picture' => true,
        'controls' => false,
        'loading' => 'lazy',
    ];

    public function __construct(
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        private readonly array $documentPlatforms,
        private readonly FilesystemOperator $documentsStorage,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly LoggerInterface $logger,
        private readonly RandomImageFinder $randomImageFinder,
        private readonly DocumentFactory $documentFactory,
        private readonly ?string $googleServerId = null,
        private readonly ?string $soundcloudClientId = null,
    ) {
    }

    public function adjustAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->em()->find(Document::class, $documentId);
        if (null === $document) {
            throw new ResourceNotFoundException();
        }
        if (!$document->isLocal()) {
            throw new ResourceNotFoundException('Document is not local');
        }

        // Assign document
        $this->assignation['document'] = $document;

        // Build form and handle it
        $fileForm = $this->buildFileForm();
        $fileForm->handleRequest($request);

        // Check if form is valid
        if ($fileForm->isSubmitted() && $fileForm->isValid()) {
            $em = $this->em();

            if (null !== $document->getRawDocument()) {
                $cloneDocument = clone $document;

                // need to remove raw document BEFORE
                // setting it to cloned document
                $rawDocument = $document->getRawDocument();
                $document->setRawDocument(null);
                $em->flush();

                $cloneDocument->setRawDocument($rawDocument);
                $oldPath = $cloneDocument->getMountPath();

                /*
                 * Prefix document filename with unique id to avoid overriding original
                 * if already existing.
                 */
                $cloneDocument->setFilename('original_'.uniqid().'_'.$cloneDocument);
                $newPath = $cloneDocument->getMountPath();

                $this->documentsStorage->move($oldPath, $newPath);

                $em->persist($cloneDocument);
                $em->flush();
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $fileForm->get('editDocument')->getData();
            $this->documentFactory->setFile($uploadedFile);
            $this->documentFactory->updateDocument($document);
            $em->flush();

            // Event must be dispatched AFTER flush for async concurrency matters
            $this->dispatchEvent(
                new DocumentFileUpdatedEvent($document)
            );
            // Event must be dispatched AFTER flush for async concurrency matters
            $this->dispatchEvent(
                new DocumentUpdatedEvent($document)
            );

            $translator = $this->getTranslator();
            $msg = $translator->trans('document.%name%.updated', [
                '%name%' => (string) $document,
            ]);

            return new JsonResponse([
                'message' => $msg,
                'path' => $this->documentsStorage->publicUrl($document->getMountPath()).'?'.\random_int(10, 999),
            ]);
        }

        // Create form view and assign it
        $this->assignation['file_form'] = $fileForm->createView();

        return $this->render('@RoadizRozier/documents/adjust.html.twig', $this->assignation);
    }

    public function editAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->em()->find(Document::class, $documentId);
        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(DocumentEditType::class, $document, [
            'referer' => $this->getRequest()->get('referer'),
            'document_platforms' => $this->documentPlatforms,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->flush();
                /*
                * Update document file
                * if present
                */
                if (null !== $newDocumentFile = $form->get('newDocument')->getData()) {
                    $this->documentFactory->setFile($newDocumentFile);
                    $this->documentFactory->updateDocument($document);
                    $msg = $this->getTranslator()->trans('document.file.%name%.updated', [
                        '%name%' => (string) $document,
                    ]);
                    $this->em()->flush();
                    // Event must be dispatched AFTER flush for async concurrency matters
                    $this->dispatchEvent(
                        new DocumentFileUpdatedEvent($document)
                    );
                    $this->publishConfirmMessage($request, $msg, $document);
                }

                $msg = $this->getTranslator()->trans('document.%name%.updated', [
                    '%name%' => (string) $document,
                ]);
                $this->publishConfirmMessage($request, $msg, $document);
                $this->em()->flush();
                // Event must be dispatched AFTER flush for async concurrency matters
                $this->dispatchEvent(
                    new DocumentUpdatedEvent($document)
                );
                $this->em()->flush();

                $routeParams = ['documentId' => $document->getId()];

                if ($form->get('referer')->getData()) {
                    $routeParams = array_merge($routeParams, [
                        'referer' => $form->get('referer')->getData(),
                    ]);
                }

                /*
                * Force redirect to avoid resending form when refreshing page
                */
                return $this->redirectToRoute(
                    'documentsEditPage',
                    $routeParams
                );
            } catch (FilesystemException $exception) {
                $form->get('filename')->addError(new FormError($exception->getMessage()));
            } catch (FileException $exception) {
                $form->get('filename')->addError(new FormError($exception->getMessage()));
            }
        }

        $this->assignation['document'] = $document;
        $this->assignation['rawDocument'] = $document->getRawDocument();
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/documents/edit.html.twig', $this->assignation);
    }

    public function deleteAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_DELETE');

        /** @var Document|null $document */
        $document = $this->em()->find(Document::class, $documentId);

        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['document'] = $document;
        $form = $this->buildDeleteForm($document);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['documentId'] == $document->getId()
        ) {
            try {
                $this->dispatchEvent(
                    new DocumentDeletedEvent($document)
                );
                $this->em()->remove($document);
                $this->em()->flush();
                $msg = $this->getTranslator()->trans('document.%name%.deleted', [
                    '%name%' => (string) $document,
                ]);
                $this->publishConfirmMessage($request, $msg, $document);
            } catch (\Exception $e) {
                $msg = $this->getTranslator()->trans('document.%name%.cannot_delete', [
                    '%name%' => (string) $document,
                ]);
                $this->logger->error($e->getMessage());
                $this->publishErrorMessage($request, $msg, $document);
            }

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('documentsHomePage');
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/documents/delete.html.twig', $this->assignation);
    }

    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_DELETE');

        $documentsIds = $request->get('documents', []);
        if (count($documentsIds) <= 0) {
            throw new ResourceNotFoundException('No selected documents to delete.');
        }

        /** @var array<Document> $documents */
        $documents = $this->em()
            ->getRepository(Document::class)
            ->findBy([
                'id' => $documentsIds,
            ]);

        if (count($documents) <= 0) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['documents'] = $documents;
        $form = $this->buildBulkDeleteForm($documentsIds);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($documents as $document) {
                $this->em()->remove($document);
                $msg = $this->getTranslator()->trans(
                    'document.%name%.deleted',
                    ['%name%' => (string) $document]
                );
                $this->publishConfirmMessage($request, $msg, $document);
            }
            $this->em()->flush();

            return $this->redirectToRoute('documentsHomePage');
        }
        $this->assignation['form'] = $form->createView();
        $this->assignation['action'] = '?'.http_build_query(['documents' => $documentsIds]);
        $this->assignation['thumbnailFormat'] = $this->thumbnailFormat;

        return $this->render('@RoadizRozier/documents/bulkDelete.html.twig', $this->assignation);
    }

    /**
     * Embed external document page.
     */
    public function embedAction(Request $request, ?int $folderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        if (null !== $folderId && $folderId > 0) {
            $folder = $this->em()->find(Folder::class, $folderId);

            $this->assignation['folder'] = $folder;
        }

        /*
         * Handle main form
         */
        $form = $this->createForm(DocumentEmbedType::class, null, [
            'document_platforms' => $this->documentPlatforms,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $document = $this->embedDocument($form->getData(), $folderId);

                if (is_iterable($document)) {
                    foreach ($document as $singleDocument) {
                        $msg = $this->getTranslator()->trans('document.%name%.uploaded', [
                            '%name%' => (new UnicodeString((string) $singleDocument))->truncate(50, '...')->toString(),
                        ]);
                        $this->publishConfirmMessage($request, $msg, $singleDocument);
                        $this->dispatchEvent(
                            new DocumentCreatedEvent($singleDocument)
                        );
                    }
                } else {
                    $msg = $this->getTranslator()->trans('document.%name%.uploaded', [
                        '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
                    ]);
                    $this->publishConfirmMessage($request, $msg, $document);
                    $this->dispatchEvent(
                        new DocumentCreatedEvent($document)
                    );
                }

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute('documentsHomePage', ['folderId' => $folderId]);
            } catch (ClientExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                if (null !== $e->getResponse() && in_array($e->getResponse()->getStatusCode(), [401, 403, 404])) {
                    $form->addError(new FormError(
                        $this->getTranslator()->trans('document.media_not_found_or_private')
                    ));
                } else {
                    $form->addError(new FormError($this->getTranslator()->trans($e->getMessage())));
                }
            } catch (APINeedsAuthentificationException $e) {
                $form->addError(new FormError($this->getTranslator()->trans($e->getMessage())));
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($this->getTranslator()->trans($e->getMessage())));
            } catch (\InvalidArgumentException $e) {
                $form->addError(new FormError($this->getTranslator()->trans($e->getMessage())));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/documents/embed.html.twig', $this->assignation);
    }

    /**
     * Get random external document page.
     *
     * @throws FilesystemException
     */
    public function randomAction(Request $request, ?int $folderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        try {
            $document = $this->randomDocument($folderId);

            $msg = $this->getTranslator()->trans('document.%name%.uploaded', [
                '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
            ]);
            $this->publishConfirmMessage($request, $msg, $document);

            $this->dispatchEvent(
                new DocumentCreatedEvent($document)
            );
        } catch (\Exception $e) {
            $this->publishErrorMessage(
                $request,
                $this->getTranslator()->trans($e->getMessage())
            );
        }

        /*
         * Force redirect to avoid resending form when refreshing page
         */
        return $this->redirectToRoute('documentsHomePage', ['folderId' => $folderId]);
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
        $document = $this->em()->find(Document::class, $documentId);

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
        $document = $this->em()->find(Document::class, $documentId);

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

        if (null !== $folderId && $folderId > 0) {
            $folder = $this->em()->find(Folder::class, $folderId);

            $this->assignation['folder'] = $folder;
        }

        /*
         * Handle main form
         */
        $form = $this->buildUploadForm($folderId);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $document = $this->uploadDocument($form, $folderId);

                if (null !== $document) {
                    $msg = $this->getTranslator()->trans('document.%name%.uploaded', [
                        '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
                    ]);
                    $this->publishConfirmMessage($request, $msg, $document);

                    // Event must be dispatched AFTER flush for async concurrency matters
                    $this->dispatchEvent(
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
                    $msg = $this->getTranslator()->trans('document.cannot_persist');
                    $this->publishErrorMessage($request, $msg, $document);

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
                            $errorPerForm[$child->getName()][] = $this->getTranslator()->trans($error->getMessage());
                        }
                    }
                }

                return new JsonResponse(
                    [
                        'errors' => $errorPerForm,
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        $this->assignation['form'] = $form->createView();
        $this->assignation['maxUploadSize'] = UploadedFile::getMaxFilesize() / 1024 / 1024;

        return $this->render('@RoadizRozier/documents/upload.html.twig', $this->assignation);
    }

    /**
     * Return a node list using this document.
     *
     * @throws RuntimeError
     */
    public function usageAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');
        /** @var Document|null $document */
        $document = $this->em()->find(Document::class, $documentId);

        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['document'] = $document;
        $this->assignation['usages'] = $document->getNodesSourcesByFields();
        $this->assignation['attributes'] = $document->getAttributeDocuments()
            ->map(function (AttributeDocuments $attributeDocument) {
                return $attributeDocument->getAttribute();
            });
        $this->assignation['tags'] = $document->getTagTranslations()
            ->map(function (TagTranslationDocuments $tagTranslationDocuments) {
                return $tagTranslationDocuments->getTagTranslation()->getTag();
            });

        return $this->render('@RoadizRozier/documents/usage.html.twig', $this->assignation);
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

    private function buildFileForm(): FormInterface
    {
        $defaults = [
            'editDocument' => null,
        ];

        $builder = $this->createFormBuilder($defaults)
            ->add('editDocument', FileType::class, [
                'label' => 'overwrite.document',
                'required' => false,
                'constraints' => [
                    new File(),
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
     * @return DocumentInterface|array<DocumentInterface>
     *
     * @throws FilesystemException
     */
    private function embedDocument(array $data, ?int $folderId = null): DocumentInterface|array
    {
        $handlers = $this->documentPlatforms;

        if (
            isset($data['embedId'])
            && isset($data['embedPlatform'])
            && in_array($data['embedPlatform'], array_keys($handlers))
        ) {
            $finder = $this->embedFinderFactory->createForPlatform($data['embedPlatform'], $data['embedId']);
            if (null === $finder) {
                throw new \RuntimeException('No embed finder found for platform '.$data['embedPlatform']);
            }
            if ($finder instanceof YoutubeEmbedFinder) {
                $finder->setKey($this->googleServerId);
            }
            if ($finder instanceof SoundcloudEmbedFinder) {
                $finder->setKey($this->soundcloudClientId);
            }

            return $this->createDocumentFromFinder($finder, $folderId);
        } else {
            throw new \RuntimeException('bad.request', 1);
        }
    }

    /**
     * Download a random document.
     *
     * @throws FilesystemException
     */
    private function randomDocument(?int $folderId = null): ?DocumentInterface
    {
        if ($this->randomImageFinder instanceof EmbedFinderInterface) {
            $document = $this->createDocumentFromFinder($this->randomImageFinder, $folderId);
            if ($document instanceof DocumentInterface) {
                return $document;
            }
            if (is_array($document) && isset($document[0])) {
                return $document[0];
            }

            return null;
        }
        throw new \RuntimeException('Random image finder must be instance of '.EmbedFinderInterface::class);
    }

    /**
     * @return DocumentInterface|array<DocumentInterface>
     *
     * @throws FilesystemException
     */
    private function createDocumentFromFinder(EmbedFinderInterface $finder, ?int $folderId = null): DocumentInterface|array
    {
        $document = $finder->createDocumentFromFeed($this->em(), $this->documentFactory);

        if (null !== $folderId && $folderId > 0) {
            /** @var Folder|null $folder */
            $folder = $this->em()->find(Folder::class, $folderId);

            if (is_iterable($document)) {
                /** @var DocumentInterface $singleDocument */
                foreach ($document as $singleDocument) {
                    $singleDocument->addFolder($folder);
                    $folder->addDocument($singleDocument);
                }
            } else {
                $document->addFolder($folder);
                $folder->addDocument($document);
            }
        }
        $this->em()->flush();

        return $document;
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
            $folder = $this->em()->find(Folder::class, $folderId);
        }

        if (!empty($data['attachment'])) {
            $uploadedFile = $data['attachment']->getData();

            $this->documentFactory->setFile($uploadedFile);
            $this->documentFactory->setFolder($folder);

            if (null !== $document = $this->documentFactory->getDocument()) {
                $this->em()->flush();

                return $document;
            }
        }

        return null;
    }
}
