<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\DocumentFileUpdatedEvent;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\Forms\DocumentEditType;

final class DocumentEditController extends AbstractController
{
    public function __construct(
        private readonly array $documentPlatforms,
        private readonly DocumentFactory $documentFactory,
        private readonly TranslatorInterface $translator,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function editAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->managerRegistry
            ->getRepository(Document::class)
            ->find($documentId);
        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(DocumentEditType::class, $document, [
            'referer' => $request->get('referer'),
            'document_platforms' => $this->documentPlatforms,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->flush();
                /*
                * Update document file
                * if present
                */
                if (null !== $newDocumentFile = $form->get('newDocument')->getData()) {
                    $this->documentFactory->setFile($newDocumentFile);
                    $this->documentFactory->updateDocument($document);
                    $msg = $this->translator->trans('document.file.%name%.updated', [
                        '%name%' => (string) $document,
                    ]);
                    $this->managerRegistry->getManager()->flush();
                    // Event must be dispatched AFTER flush for async concurrency matters
                    $this->eventDispatcher->dispatch(
                        new DocumentFileUpdatedEvent($document)
                    );
                    $this->logTrail->publishConfirmMessage($request, $msg, $document);
                }

                $msg = $this->translator->trans('document.%name%.updated', [
                    '%name%' => (string) $document,
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $document);
                $this->managerRegistry->getManager()->flush();
                // Event must be dispatched AFTER flush for async concurrency matters
                $this->eventDispatcher->dispatch(
                    new DocumentUpdatedEvent($document)
                );
                $this->managerRegistry->getManager()->flush();

                $routeParams = ['documentId' => $document->getId()];

                if ($form->get('referer')->getData()) {
                    $routeParams = array_merge($routeParams, [
                        'referer' => $form->get('referer')->getData(),
                    ]);
                }

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

        return $this->render('@RoadizRozier/documents/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
            'rawDocument' => $document->getRawDocument(),
        ]);
    }
}
