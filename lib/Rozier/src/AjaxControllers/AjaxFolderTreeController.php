<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Widgets\FolderTreeWidget;
use Themes\Rozier\Widgets\TreeWidgetFactory;

final class AjaxFolderTreeController extends AbstractAjaxController
{
    public function __construct(private readonly TreeWidgetFactory $treeWidgetFactory)
    {
    }

    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');
        $translation = $this->getTranslation($request);

        /** @var FolderTreeWidget|null $folderTree */
        $folderTree = null;

        switch ($request->get("_action")) {
            /*
             * Inner folder edit for folderTree
             */
            case 'requestFolderTree':
                if ($request->get('parentFolderId') > 0) {
                    $folder = $this->em()
                                ->find(
                                    Folder::class,
                                    (int) $request->get('parentFolderId')
                                );
                } else {
                    $folder = null;
                }

                $folderTree = $this->treeWidgetFactory->createFolderTree($folder, $translation);

                $this->assignation['mainFolderTree'] = false;

                break;
            /*
             * Main panel tree folderTree
             */
            case 'requestMainFolderTree':
                $parent = null;
                $folderTree = $this->treeWidgetFactory->createFolderTree($parent, $translation);
                $this->assignation['mainFolderTree'] = true;
                break;
        }

        $this->assignation['folderTree'] = $folderTree;

        $responseArray = [
            'statusCode' => '200',
            'status' => 'success',
            'folderTree' => $this->getTwig()->render('@RoadizRozier/widgets/folderTree/folderTree.html.twig', $this->assignation),
        ];

        return new JsonResponse(
            $responseArray
        );
    }
}
