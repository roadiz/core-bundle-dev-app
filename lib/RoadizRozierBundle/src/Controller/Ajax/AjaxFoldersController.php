<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\EntityHandler\FolderHandler;
use RZ\Roadiz\CoreBundle\Repository\FolderRepository;
use RZ\Roadiz\RozierBundle\Model\PositionDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxFoldersController extends AbstractAjaxController
{
    use UpdatePositionTrait;

    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly HandlerFactoryInterface $handlerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /*
     * Handle AJAX edition requests for Folder
     * such as coming from tag-tree widgets.
     */
    #[Route(
        path: '/rz-admin/ajax/folder/position',
        name: 'foldersPositionAjax',
        methods: ['POST'],
        format: 'json',
    )]
    public function editPositionAction(
        #[MapRequestPayload]
        PositionDto $positionDto,
    ): JsonResponse {
        $this->validateCsrfToken($positionDto->csrfToken);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = $this->folderRepository->find($positionDto->id);

        if (null === $folder) {
            throw $this->createNotFoundException('Folder '.$positionDto->id.' does not exists');
        }

        $this->updatePositionAndParent($positionDto, $folder, $this->folderRepository);

        // Apply position update before cleaning
        $this->managerRegistry->getManager()->flush();

        /** @var FolderHandler $handler */
        $handler = $this->handlerFactory->getHandler($folder);
        $handler->cleanPositions();

        $this->managerRegistry->getManager()->flush();

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => ('Folder '.$positionDto->id.' edited '),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    #[Route(
        path: '/rz-admin/ajax/folder/search',
        name: 'foldersAjaxSearch',
        methods: ['GET'],
        format: 'json'
    )]
    public function searchAction(
        #[MapQueryParameter]
        string $search = '',
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $responseArray = [];

        $search = strip_tags($search);
        if (empty($search)) {
            return new JsonResponse(
                [],
                Response::HTTP_OK
            );
        }

        $folders = $this->managerRegistry
                    ->getRepository(Folder::class)
                    ->searchBy(
                        $search,
                        [],
                        [],
                        10
                    );
        /** @var Folder $folder */
        foreach ($folders as $folder) {
            $responseArray[] = $folder->getFullPath();
        }

        return new JsonResponse(
            $responseArray,
            Response::HTTP_OK
        );
    }
}
