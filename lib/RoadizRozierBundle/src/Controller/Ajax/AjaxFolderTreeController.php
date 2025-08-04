<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\RozierBundle\Widget\FolderTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');
        $translation = $this->getTranslation($request);

        /** @var FolderTreeWidget|null $folderTree */
        $folderTree = null;
        $assignation = [];

        switch ($request->get('_action')) {
            case 'requestFolderTree':
                if ($request->get('parentFolderId') > 0) {
                    $folder = $this->managerRegistry
                        ->getRepository(Folder::class)
                        ->find((int) $request->get('parentFolderId'));
                } else {
                    $folder = null;
                }

                $folderTree = $this->treeWidgetFactory->createFolderTree($folder, $translation);

                $assignation['mainFolderTree'] = false;
                break;
            case 'requestMainFolderTree':
                $parent = null;
                $folderTree = $this->treeWidgetFactory->createFolderTree($parent, $translation);
                $assignation['mainFolderTree'] = true;
                break;
        }

        $assignation['folderTree'] = $folderTree;

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'folderTree' => $this->twig->render('@RoadizRozier/widgets/folderTree/folderTree.html.twig', $assignation),
        ]);
    }
}
