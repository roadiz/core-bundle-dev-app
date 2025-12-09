<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Repository\FolderRepository;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\DocumentInFolderEvent;
use RZ\Roadiz\Documents\Events\DocumentOutFolderEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentPublicListController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly FolderRepository $folderRepository,
        private readonly array $documentPlatforms,
    ) {
    }

    protected function getFolder(?int $folderId): ?Folder
    {
        if (null === $folderId || $folderId <= 0) {
            return null;
        }

        return $this->folderRepository->find($folderId);
    }

    protected function getPreFilters(Request $request): array
    {
        return [
            'private' => false,
            'raw' => false,
        ];
    }

    public function getAssignation(): array
    {
        return [
            'pageTitle' => 'documents',
            'availablePlatforms' => $this->documentPlatforms,
            'displayPrivateDocuments' => false,
        ];
    }

    public function indexAction(Request $request, ?int $folderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');
        $assignation = $this->getAssignation();

        /** @var Translation $translation */
        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findDefault();

        $prefilters = $this->getPreFilters($request);

        $folder = $this->getFolder($folderId);
        if (null !== $folder) {
            $prefilters['folders'] = [
                $folder,
                // also show documents in child folders
                ...$this->folderRepository->findAllChildrenFromFolder($folder),
            ];
            $assignation['folder'] = $folder;
        }

        $type = $request->query->get('type');
        $embedPlatform = $request->query->get('embedPlatform');

        if (\is_string($type) && '' !== $type) {
            $prefilters['mimeType'] = trim($type);
            $assignation['mimeType'] = trim($type);
        }

        if (\is_string($embedPlatform) && '' !== $embedPlatform) {
            $prefilters['embedPlatform'] = trim($embedPlatform);
            $assignation['embedPlatform'] = trim($embedPlatform);
        }

        /*
         * Handle bulk folder form
         */
        $joinFolderForm = $this->buildLinkFoldersForm();
        $joinFolderForm->handleRequest($request);
        if ($joinFolderForm->isSubmitted() && $joinFolderForm->isValid()) {
            $data = $joinFolderForm->getData();
            $submitFolder = $joinFolderForm->get('submitFolder');
            $submitUnfolder = $joinFolderForm->get('submitUnfolder');
            if ($submitFolder instanceof ClickableInterface && $submitFolder->isClicked()) {
                $msg = $this->joinFolder($data);
            } elseif ($submitUnfolder instanceof ClickableInterface && $submitUnfolder->isClicked()) {
                $msg = $this->leaveFolder($data);
            } else {
                $msg = $this->translator->trans('wrong.request');
            }

            $this->logTrail->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute(
                'documentsHomePage',
                ['folderId' => $folderId]
            );
        }
        $assignation['joinFolderForm'] = $joinFolderForm->createView();

        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Document::class,
            $prefilters,
            ['createdAt' => 'DESC']
        );
        $sessionListFilter = new SessionListFilters('documents_item_per_page', 50);
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

        $assignation['filters'] = $listManager->getAssignation();
        $assignation['documents'] = $listManager->getEntities();
        $assignation['translation'] = $translation;
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

        return $this->render($this->getListingTemplate($request), $assignation);
    }

    protected function getListingTemplate(Request $request): string
    {
        if ('1' === $request->query->get('list')) {
            return '@RoadizRozier/documents/list-table.html.twig';
        }

        return '@RoadizRozier/documents/list.html.twig';
    }

    private function buildLinkFoldersForm(): FormInterface
    {
        $builder = $this->formFactory->createNamedBuilder('folderForm')
            ->add('documentsId', HiddenType::class, [
                'attr' => ['class' => 'bulk-form-value'],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('folderPaths', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'rz-folder-autocomplete',
                    'placeholder' => 'list.folders.to_link',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('submitFolder', SubmitType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'uk-button uk-button-primary',
                    'title' => 'link.folders',
                    'data-uk-tooltip' => '{animation:true}',
                ],
            ])
            ->add('submitUnfolder', SubmitType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'uk-button',
                    'title' => 'unlink.folders',
                    'data-uk-tooltip' => '{animation:true}',
                ],
            ]);

        return $builder->getForm();
    }

    /**
     * @return string Status message
     */
    private function joinFolder(array $data): string
    {
        $msg = $this->translator->trans('no_documents.linked_to.folders');

        if (
            !empty($data['documentsId'])
            && !empty($data['folderPaths'])
        ) {
            $documentsIds = json_decode($data['documentsId'], true, flags: JSON_THROW_ON_ERROR);

            $documents = $this->managerRegistry
                ->getRepository(Document::class)
                ->findBy([
                    'id' => $documentsIds,
                ]);

            if (!is_array($data['folderPaths'])) {
                $folderPaths = explode(',', (string) $data['folderPaths']);
            } else {
                $folderPaths = $data['folderPaths'];
            }
            $folderPaths = array_filter($folderPaths);

            foreach ($folderPaths as $path) {
                /** @var Folder $folder */
                $folder = $this->managerRegistry
                    ->getRepository(Folder::class)
                    ->findOrCreateByPath($path);

                /*
                 * Add each selected documents
                 */
                foreach ($documents as $document) {
                    $folder->addDocument($document);
                }
            }

            $this->managerRegistry->getManagerForClass(Document::class)?->flush();
            $msg = $this->translator->trans('documents.linked_to.folders');

            /*
             * Dispatch events
             */
            foreach ($documents as $document) {
                $this->eventDispatcher->dispatch(
                    new DocumentInFolderEvent($document)
                );
            }
        }

        return $msg;
    }

    /**
     * @return string Status message
     */
    private function leaveFolder(array $data): string
    {
        $msg = $this->translator->trans('no_documents.removed_from.folders');

        if (
            !empty($data['documentsId'])
            && !empty($data['folderPaths'])
        ) {
            $documentsIds = json_decode($data['documentsId'], true, flags: JSON_THROW_ON_ERROR);

            $documents = $this->managerRegistry
                ->getRepository(Document::class)
                ->findBy([
                    'id' => $documentsIds,
                ]);

            $folderPaths = explode(',', (string) $data['folderPaths']);
            $folderPaths = array_filter($folderPaths);

            foreach ($folderPaths as $path) {
                /** @var Folder $folder */
                $folder = $this->managerRegistry
                    ->getRepository(Folder::class)
                    ->findByPath($path);

                if (null !== $folder) {
                    /*
                     * Add each selected documents
                     */
                    foreach ($documents as $document) {
                        $folder->removeDocument($document);
                    }
                }
            }
            $this->managerRegistry->getManagerForClass(Document::class)?->flush();
            $msg = $this->translator->trans('documents.removed_from.folders');

            /*
             * Dispatch events
             */
            foreach ($documents as $document) {
                $this->eventDispatcher->dispatch(
                    new DocumentOutFolderEvent($document)
                );
            }
        }

        return $msg;
    }
}
