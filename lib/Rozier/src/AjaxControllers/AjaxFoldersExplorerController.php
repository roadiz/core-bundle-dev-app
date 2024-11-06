<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AjaxFoldersExplorerController extends AbstractAjaxController
{
    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folders = $this->em()
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
