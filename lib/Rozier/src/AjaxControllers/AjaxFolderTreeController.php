<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Widgets\FolderTreeWidget;
use Themes\Rozier\Widgets\TreeWidgetFactory;

class AjaxFolderTreeController extends AbstractAjaxController
{
    private TreeWidgetFactory $treeWidgetFactory;

    public function __construct(TreeWidgetFactory $treeWidgetFactory)
    {
        $this->treeWidgetFactory = $treeWidgetFactory;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getTreeAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

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

                $folderTree = $this->treeWidgetFactory->createFolderTree($folder);

                $this->assignation['mainFolderTree'] = false;

                break;
            /*
             * Main panel tree folderTree
             */
            case 'requestMainFolderTree':
                $parent = null;
                $folderTree = $this->treeWidgetFactory->createFolderTree($parent);
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
