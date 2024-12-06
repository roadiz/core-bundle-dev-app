<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\Documents\DocumentArchiver;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\Forms\FolderTranslationType;
use Themes\Rozier\Forms\FolderType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class FoldersController extends RozierApp
{
    public function __construct(private readonly DocumentArchiver $documentArchiver)
    {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $listManager = $this->createEntityListManager(
            Folder::class
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['folders'] = $listManager->getEntities();

        return $this->render('@RoadizRozier/folders/list.html.twig', $this->assignation);
    }

    /**
     * Return a creation form for requested folder.
     *
     * @throws RuntimeError
     */
    public function addAction(Request $request, ?int $parentFolderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = new Folder();

        if (null !== $parentFolderId) {
            $parentFolder = $this->em()->find(Folder::class, $parentFolderId);
            if (null !== $parentFolder) {
                $folder->setParent($parentFolder);
            }
        }
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var Translation $translation */
                $translation = $this->em()->getRepository(Translation::class)->findDefault();
                $folderTranslation = new FolderTranslation($folder, $translation);
                $this->em()->persist($folder);
                $this->em()->persist($folderTranslation);

                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    'folder.%name%.created',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->publishConfirmMessage($request, $msg, $folder);

                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(
                    new FolderCreatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersHomePage');
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/folders/add.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested folder.
     *
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $folderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Folder|null $folder */
        $folder = $this->em()->find(Folder::class, $folderId);

        if (null === $folder || $folder->isLocked()) {
            throw new ResourceNotFoundException('Folder does not exist or is locked');
        }

        $form = $this->createForm(FormType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->remove($folder);
                $this->em()->flush();
                $msg = $this->getTranslator()->trans(
                    'folder.%name%.deleted',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->publishConfirmMessage($request, $msg, $folder);

                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(
                    new FolderDeletedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersHomePage');
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['folder'] = $folder;

        return $this->render('@RoadizRozier/folders/delete.html.twig', $this->assignation);
    }

    /**
     * Return an edition form for requested folder.
     *
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $folderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Folder|null $folder */
        $folder = $this->em()->find(Folder::class, $folderId);

        if (null === $folder) {
            throw new ResourceNotFoundException();
        }

        /** @var Translation $translation */
        $translation = $this->em()
            ->getRepository(Translation::class)
            ->findDefault();

        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->flush();
                $msg = $this->getTranslator()->trans(
                    'folder.%name%.updated',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->publishConfirmMessage($request, $msg, $folder);
                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(
                    new FolderUpdatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersEditPage', ['folderId' => $folderId]);
        }

        $this->assignation['folder'] = $folder;
        $this->assignation['form'] = $form->createView();
        $this->assignation['translation'] = $translation;

        return $this->render('@RoadizRozier/folders/edit.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function editTranslationAction(Request $request, int $folderId, int $translationId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var TranslationRepository $translationRepository */
        $translationRepository = $this->em()->getRepository(Translation::class);

        /** @var Folder|null $folder */
        $folder = $this->em()->find(Folder::class, $folderId);

        /** @var Translation|null $translation */
        $translation = $this->em()->find(Translation::class, $translationId);

        if (null === $folder || null === $translation) {
            throw new ResourceNotFoundException();
        }

        /** @var FolderTranslation|null $folderTranslation */
        $folderTranslation = $this->em()
            ->getRepository(FolderTranslation::class)
            ->findOneBy([
                'folder' => $folder,
                'translation' => $translation,
            ]);

        if (null === $folderTranslation) {
            $folderTranslation = new FolderTranslation($folder, $translation);
            $this->em()->persist($folderTranslation);
        }

        $form = $this->createForm(FolderTranslationType::class, $folderTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /*
                 * Update folder slug if not locked
                 * only from default translation.
                 */
                $newFolderName = StringHandler::slugify($folderTranslation->getName());
                if ($folder->getFolderName() !== $newFolderName) {
                    if (
                        !$folder->isLocked()
                        && $translation->isDefaultTranslation()
                        && !$this->folderNameExists($newFolderName)
                    ) {
                        $folder->setFolderName($folderTranslation->getName());
                    }
                }

                $this->em()->flush();
                $msg = $this->getTranslator()->trans(
                    'folder.%name%.updated',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->publishConfirmMessage($request, $msg, $folder);
                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(
                    new FolderUpdatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersEditTranslationPage', [
                'folderId' => $folderId,
                'translationId' => $translationId,
            ]);
        }

        $this->assignation['folder'] = $folder;
        $this->assignation['translation'] = $translation;
        $this->assignation['form'] = $form->createView();
        $this->assignation['available_translations'] = $translationRepository->findAll();
        $this->assignation['translations'] = $translationRepository->findAvailableTranslationsForFolder($folder);

        return $this->render('@RoadizRozier/folders/edit.html.twig', $this->assignation);
    }

    protected function folderNameExists(string $name): bool
    {
        $entity = $this->em()->getRepository(Folder::class)->findOneByFolderName($name);

        return null !== $entity;
    }

    /**
     * Return a ZipArchive of requested folder.
     */
    public function downloadAction(int $folderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Folder|null $folder */
        $folder = $this->em()->find(Folder::class, $folderId);

        if (null === $folder) {
            throw new ResourceNotFoundException();
        }

        $documents = $this->em()
            ->getRepository(Document::class)
            ->findBy([
                'folders' => [$folder],
            ]);

        return $this->documentArchiver->archiveAndServe(
            $documents,
            $folder->getFolderName().'_'.date('YmdHi'),
            true
        );
    }
}
