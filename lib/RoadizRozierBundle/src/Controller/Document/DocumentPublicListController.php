<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Documents\Events\DocumentInFolderEvent;
use RZ\Roadiz\Documents\Events\DocumentOutFolderEvent;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Utils\SessionListFilters;
use Twig\Error\RuntimeError;

class DocumentPublicListController extends RozierApp
{
    private array $documentPlatforms;

    /**
     * @param array $documentPlatforms
     */
    public function __construct(array $documentPlatforms)
    {
        $this->documentPlatforms = $documentPlatforms;
    }

    protected function getFolder(?int $folderId): ?Folder
    {
        if (null === $folderId || $folderId <= 0) {
            return null;
        }
        return $this->em()->find(Folder::class, $folderId);
    }

    protected function getPreFilters(Request $request): array
    {
        return [
            'private' => false,
            'raw' => false,
        ];
    }

    public function prepareBaseAssignation(): static
    {
        parent::prepareBaseAssignation();

        $this->assignation['pageTitle'] = 'documents';
        $this->assignation['availablePlatforms'] = $this->documentPlatforms;
        $this->assignation['displayPrivateDocuments'] = false;

        return $this;
    }

    /**
     * @param Request $request
     * @param int|null $folderId
     * @return Response
     * @throws RuntimeError
     */
    public function indexAction(Request $request, ?int $folderId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Translation $translation */
        $translation = $this->em()
            ->getRepository(Translation::class)
            ->findDefault();

        $prefilters = $this->getPreFilters($request);

        $folder = $this->getFolder($folderId);
        if (null !== $folder) {
            $prefilters['folders'] = [$folder];
            $this->assignation['folder'] = $folder;
        }

        if (
            $request->query->has('type') &&
            $request->query->get('type', '') !== ''
        ) {
            $prefilters['mimeType'] = trim($request->query->get('type', ''));
            $this->assignation['mimeType'] = trim($request->query->get('type', ''));
        }

        if (
            $request->query->has('embedPlatform') &&
            $request->query->get('embedPlatform', '') !== ''
        ) {
            $prefilters['embedPlatform'] = trim($request->query->get('embedPlatform', ''));
            $this->assignation['embedPlatform'] = trim($request->query->get('embedPlatform', ''));
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
                $msg = $this->getTranslator()->trans('wrong.request');
            }

            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute(
                'documentsHomePage',
                ['folderId' => $folderId]
            );
        }
        $this->assignation['joinFolderForm'] = $joinFolderForm->createView();

        $listManager = $this->createEntityListManager(
            Document::class,
            $prefilters,
            ['createdAt' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(static::DEFAULT_ITEM_PER_PAGE);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('documents_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['documents'] = $listManager->getEntities();
        $this->assignation['translation'] = $translation;
        $this->assignation['thumbnailFormat'] = [
            'quality' => 50,
            'fit' => '128x128',
            'sharpen' => 5,
            'inline' => false,
            'picture' => true,
            'loading' => 'lazy',
        ];

        return $this->render($this->getListingTemplate($request), $this->assignation);
    }

    protected function getListingTemplate(Request $request): string
    {
        if ($request->query->get('list') === '1') {
            return '@RoadizRozier/documents/list-table.html.twig';
        }
        return '@RoadizRozier/documents/list.html.twig';
    }

    /**
     * @return FormInterface
     */
    private function buildLinkFoldersForm(): FormInterface
    {
        $builder = $this->createNamedFormBuilder('folderForm')
            ->add('documentsId', HiddenType::class, [
                'attr' => ['class' => 'document-id-bulk-folder'],
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
                    'data-uk-tooltip' => "{animation:true}",
                ],
            ])
            ->add('submitUnfolder', SubmitType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'uk-button',
                    'title' => 'unlink.folders',
                    'data-uk-tooltip' => "{animation:true}",
                ],
            ]);

        return $builder->getForm();
    }

    /**
     * @param array $data
     * @return string Status message
     */
    private function joinFolder(array $data): string
    {
        $msg = $this->getTranslator()->trans('no_documents.linked_to.folders');

        if (
            !empty($data['documentsId']) &&
            !empty($data['folderPaths'])
        ) {
            $documentsIds = explode(',', $data['documentsId']);

            $documents = $this->em()
                ->getRepository(Document::class)
                ->findBy([
                    'id' => $documentsIds,
                ]);

            $folderPaths = explode(',', $data['folderPaths']);
            $folderPaths = array_filter($folderPaths);

            foreach ($folderPaths as $path) {
                /** @var Folder $folder */
                $folder = $this->em()
                    ->getRepository(Folder::class)
                    ->findOrCreateByPath($path);

                /*
                 * Add each selected documents
                 */
                foreach ($documents as $document) {
                    $folder->addDocument($document);
                }
            }

            $this->em()->flush();
            $msg = $this->getTranslator()->trans('documents.linked_to.folders');

            /*
             * Dispatch events
             */
            foreach ($documents as $document) {
                $this->dispatchEvent(
                    new DocumentInFolderEvent($document)
                );
            }
        }

        return $msg;
    }

    /**
     * @param array $data
     * @return string Status message
     */
    private function leaveFolder(array $data): string
    {
        $msg = $this->getTranslator()->trans('no_documents.removed_from.folders');

        if (
            !empty($data['documentsId']) &&
            !empty($data['folderPaths'])
        ) {
            $documentsIds = explode(',', $data['documentsId']);

            $documents = $this->em()
                ->getRepository(Document::class)
                ->findBy([
                    'id' => $documentsIds,
                ]);

            $folderPaths = explode(',', $data['folderPaths']);
            $folderPaths = array_filter($folderPaths);

            foreach ($folderPaths as $path) {
                /** @var Folder $folder */
                $folder = $this->em()
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
            $this->em()->flush();
            $msg = $this->getTranslator()->trans('documents.removed_from.folders');

            /*
             * Dispatch events
             */
            foreach ($documents as $document) {
                $this->dispatchEvent(
                    new DocumentOutFolderEvent($document)
                );
            }
        }

        return $msg;
    }
}
