<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax\Tree;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use RZ\Roadiz\RozierBundle\Widget\FolderTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class AjaxFolderTreeController extends AbstractAjaxController
{
    public function __construct(
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly Environment $twig,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    #[Route(
        path: '/rz-admin/ajax/folders/tree',
        name: 'foldersTreeAjax',
        methods: ['GET'],
        format: 'json'
    )]
    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');
        $translation = $this->getTranslation($request);

        /** @var FolderTreeWidget|null $folderTree */
        $folderTree = null;
        $assignation = [];

        switch ($request->query->get('_action')) {
            case 'requestFolderTree':
                if ($request->query->get('parentFolderId') > 0) {
                    $folder = $this->managerRegistry
                        ->getRepository(Folder::class)
                        ->find((int) $request->query->get('parentFolderId'));
                } else {
                    $folder = null;
                }

                $folderTree = $this->treeWidgetFactory->createFolderTree($folder, $translation);

                $assignation['mainTree'] = false;
                break;
            case 'requestMainTree':
                $parent = null;
                $folderTree = $this->treeWidgetFactory->createFolderTree($parent, $translation);
                $assignation['mainTree'] = true;
                break;
        }

        $assignation['tree'] = $folderTree;
        $assignation['tree_type'] = 'folder';

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'tree_type' => $assignation['tree_type'],
            'folderTree' => $this->twig->render('@RoadizRozier/widgets/tree/rz_tree_wrapper_auto.html.twig', $assignation),
        ]);
    }
}
