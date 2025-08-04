<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\DocumentArchiver;
use RZ\Roadiz\RozierBundle\Form\FolderTranslationType;
use RZ\Roadiz\RozierBundle\Form\FolderType;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class FolderController extends AbstractController
{
    public function __construct(
        private readonly DocumentArchiver $documentArchiver,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Folder::class
        );
        $listManager->handle();

        return $this->render('@RoadizRozier/folders/list.html.twig', [
            'filters' => $listManager->getAssignation(),
            'folders' => $listManager->getEntities(),
        ]);
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
            $parentFolder = $this->managerRegistry->getRepository(Folder::class)->find($parentFolderId);
            if (null !== $parentFolder) {
                $folder->setParent($parentFolder);
            }
        }
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var Translation $translation */
                $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
                $folderTranslation = new FolderTranslation($folder, $translation);
                $manager = $this->managerRegistry->getManager();
                $manager->persist($folder);
                $manager->persist($folderTranslation);

                $manager->flush();

                $msg = $this->translator->trans(
                    'folder.%name%.created',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $folder);

                /*
                 * Dispatch event
                 */
                $this->dispatcher->dispatch(
                    new FolderCreatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersHomePage');
        }

        return $this->render('@RoadizRozier/folders/add.html.twig', [
            'form' => $form->createView(),
        ]);
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
        $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);

        if (null === $folder || $folder->isLocked()) {
            throw new ResourceNotFoundException('Folder does not exist or is locked');
        }

        $form = $this->createForm(FormType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager = $this->managerRegistry->getManager();
                $manager->remove($folder);
                $manager->flush();
                $msg = $this->translator->trans(
                    'folder.%name%.deleted',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $folder);

                /*
                 * Dispatch event
                 */
                $this->dispatcher->dispatch(
                    new FolderDeletedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersHomePage');
        }

        return $this->render('@RoadizRozier/folders/delete.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
        ]);
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
        $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);

        if (null === $folder) {
            throw new ResourceNotFoundException();
        }

        /** @var Translation $translation */
        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findDefault();

        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->flush();
                $msg = $this->translator->trans(
                    'folder.%name%.updated',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $folder);
                /*
                 * Dispatch event
                 */
                $this->dispatcher->dispatch(
                    new FolderUpdatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersEditPage', ['folderId' => $folderId]);
        }

        return $this->render('@RoadizRozier/folders/edit.html.twig', [
            'folder' => $folder,
            'translation' => $translation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws RuntimeError
     */
    public function editTranslationAction(Request $request, int $folderId, int $translationId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var TranslationRepository $translationRepository */
        $translationRepository = $this->managerRegistry->getRepository(Translation::class);

        /** @var Folder|null $folder */
        $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);

        /** @var Translation|null $translation */
        $translation = $this->managerRegistry->getRepository(Translation::class)->find($translationId);

        if (null === $folder || null === $translation) {
            throw new ResourceNotFoundException();
        }

        $manager = $this->managerRegistry->getManager();

        /** @var FolderTranslation|null $folderTranslation */
        $folderTranslation = $this->managerRegistry
            ->getRepository(FolderTranslation::class)
            ->findOneBy([
                'folder' => $folder,
                'translation' => $translation,
            ]);

        if (null === $folderTranslation) {
            $folderTranslation = new FolderTranslation($folder, $translation);
            $manager->persist($folderTranslation);
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

                $manager->flush();
                $msg = $this->translator->trans(
                    'folder.%name%.updated',
                    ['%name%' => $folder->getFolderName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $folder);
                /*
                 * Dispatch event
                 */
                $this->dispatcher->dispatch(
                    new FolderUpdatedEvent($folder)
                );
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $folder);
            }

            return $this->redirectToRoute('foldersEditTranslationPage', [
                'folderId' => $folderId,
                'translationId' => $translationId,
            ]);
        }

        return $this->render('@RoadizRozier/folders/edit.html.twig', [
            'folder' => $folder,
            'translation' => $translation,
            'form' => $form->createView(),
            'available_translations' => $translationRepository->findAll(),
            'translations' => $translationRepository->findAvailableTranslationsForFolder($folder),
        ]);
    }

    protected function folderNameExists(string $name): bool
    {
        $entity = $this->managerRegistry->getRepository(Folder::class)->findOneByFolderName($name);

        return null !== $entity;
    }

    /**
     * Return a ZipArchive of requested folder.
     */
    public function downloadAction(int $folderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Folder|null $folder */
        $folder = $this->managerRegistry->getRepository(Folder::class)->find($folderId);

        if (null === $folder) {
            throw new ResourceNotFoundException();
        }

        $documents = $this->managerRegistry
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
