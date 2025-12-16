<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Events\DocumentFileUpdatedEvent;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class DocumentAdjustController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FilesystemOperator $documentsStorage,
        private readonly DocumentFactory $documentFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function adjustAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $document = $this->managerRegistry->getRepository(Document::class)->find($documentId);
        if (!$document instanceof Document) {
            throw new ResourceNotFoundException();
        }
        if (!$document->isLocal()) {
            throw new ResourceNotFoundException('Document is not local');
        }

        // Build form and handle it
        $fileForm = $this->buildFileForm();
        $fileForm->handleRequest($request);

        // Check if form is valid
        if ($fileForm->isSubmitted() && $fileForm->isValid()) {
            $em = $this->managerRegistry->getManager();

            if (null !== $document->getRawDocument()) {
                $cloneDocument = clone $document;

                // need to remove raw document BEFORE
                // setting it to cloned document
                $rawDocument = $document->getRawDocument();
                $document->setRawDocument(null);
                $em->flush();

                $cloneDocument->setRawDocument($rawDocument);
                $oldPath = $cloneDocument->getMountPath() ?? throw new \RuntimeException('Document has no mount path.');

                /*
                 * Prefix document filename with unique id to avoid overriding original
                 * if already existing.
                 */
                $cloneDocument->setFilename('original_'.uniqid().'_'.$cloneDocument);
                $newPath = $cloneDocument->getMountPath() ?? throw new \RuntimeException('Cloned document has no mount path.');

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
            $this->eventDispatcher->dispatch(
                new DocumentFileUpdatedEvent($document)
            );
            // Event must be dispatched AFTER flush for async concurrency matters
            $this->eventDispatcher->dispatch(
                new DocumentUpdatedEvent($document)
            );

            $msg = $this->translator->trans('document.%name%.updated', [
                '%name%' => (string) $document,
            ]);
            $mountPath = $document->getMountPath() ?? throw new \RuntimeException('Document has no mount path.');

            return new JsonResponse([
                'message' => $msg,
                'path' => $this->documentsStorage->publicUrl($mountPath).'?'.\random_int(10, 999),
            ]);
        }

        return $this->render('@RoadizRozier/documents/adjust.html.twig', [
            'file_form' => $fileForm->createView(),
            'document' => $document,
        ]);
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
}
