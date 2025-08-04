<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Only used to display folders list in document explorer filtering aside.
 */
final class AjaxFoldersExplorerController extends AbstractAjaxController
{
    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folders = $this->managerRegistry
                        ->getRepository(Folder::class)
                        ->findBy(
                            [
                                'parent' => null,
                            ],
                            [
                                'position' => 'ASC',
                            ]
                        );

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'folders' => $this->recurseFolders($folders),
        ]);
    }

    protected function recurseFolders(?iterable $folders = null): array
    {
        $foldersArray = [];
        if (null !== $folders) {
            /** @var Folder $folder */
            foreach ($folders as $folder) {
                $children = $this->recurseFolders($folder->getChildren());
                $foldersArray[] = [
                    'id' => $folder->getId(),
                    'name' => $folder->getName(),
                    'folderName' => $folder->getFolderName(),
                    'children' => $children,
                ];
            }
        }

        return $foldersArray;
    }
}
