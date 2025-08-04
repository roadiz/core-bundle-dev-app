<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\MediaFinder\SoundcloudEmbedFinder;
use RZ\Roadiz\CoreBundle\Document\MediaFinder\YoutubeEmbedFinder;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Exceptions\APINeedsAuthentificationException;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderInterface;
use RZ\Roadiz\Documents\MediaFinders\RandomImageFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\RozierBundle\Form\DocumentEmbedType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class DocumentEmbedController extends AbstractController
{
    public function __construct(
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly array $documentPlatforms,
        private readonly LoggerInterface $logger,
        private readonly RandomImageFinder $randomImageFinder,
        private readonly DocumentFactory $documentFactory,
        private readonly TranslatorInterface $translator,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly ?string $googleServerId = null,
        private readonly ?string $soundcloudClientId = null,
    ) {
    }

    /**
     * Embed external document page.
     */
    public function embedAction(Request $request, ?int $folderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = null;
        if (null !== $folderId && $folderId > 0) {
            $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);
        }

        $form = $this->createForm(DocumentEmbedType::class, null, [
            'document_platforms' => $this->documentPlatforms,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $document = $this->embedDocument($form->getData(), $folderId);

                if (is_iterable($document)) {
                    foreach ($document as $singleDocument) {
                        $msg = $this->translator->trans('document.%name%.uploaded', [
                            '%name%' => (new UnicodeString((string) $singleDocument))->truncate(50, '...')->toString(),
                        ]);
                        $this->logTrail->publishConfirmMessage($request, $msg, $singleDocument);
                        $this->eventDispatcher->dispatch(
                            new DocumentCreatedEvent($singleDocument)
                        );
                    }
                } else {
                    $msg = $this->translator->trans('document.%name%.uploaded', [
                        '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
                    ]);
                    $this->logTrail->publishConfirmMessage($request, $msg, $document);
                    $this->eventDispatcher->dispatch(
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
                        $this->translator->trans('document.media_not_found_or_private')
                    ));
                } else {
                    $form->addError(new FormError($this->translator->trans($e->getMessage())));
                }
            } catch (APINeedsAuthentificationException|\RuntimeException|\InvalidArgumentException $e) {
                $form->addError(new FormError($this->translator->trans($e->getMessage())));
            }
        }

        return $this->render('@RoadizRozier/documents/embed.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
        ]);
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

            $msg = $this->translator->trans('document.%name%.uploaded', [
                '%name%' => (new UnicodeString((string) $document))->truncate(50, '...')->toString(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $document);

            $this->eventDispatcher->dispatch(
                new DocumentCreatedEvent($document)
            );
        } catch (\Exception $e) {
            $this->logTrail->publishErrorMessage(
                $request,
                $this->translator->trans($e->getMessage())
            );
        }

        return $this->redirectToRoute('documentsHomePage', ['folderId' => $folderId]);
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
     * @return DocumentInterface|array<DocumentInterface>
     *
     * @throws FilesystemException
     */
    private function createDocumentFromFinder(EmbedFinderInterface $finder, ?int $folderId = null): DocumentInterface|array
    {
        $document = $finder->createDocumentFromFeed($this->managerRegistry->getManager(), $this->documentFactory);

        if (null !== $folderId && $folderId > 0) {
            /** @var Folder|null $folder */
            $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);

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
        $this->managerRegistry->getManager()->flush();

        return $document;
    }
}
