<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\EntityHandler\FolderHandler;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class AjaxFoldersController extends AbstractAjaxController
{
    public function __construct(
        private readonly HandlerFactoryInterface $handlerFactory,
        SerializerInterface $serializer
    ) {
        parent::__construct($serializer);
    }

    /*
     * Handle AJAX edition requests for Folder
     * such as coming from tag-tree widgets.
     */
    public function editAction(Request $request, int $folderId): JsonResponse
    {
        $this->validateRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = $this->em()->find(Folder::class, (int) $folderId);

        if ($folder === null) {
            throw $this->createNotFoundException($this->getTranslator()->trans('folder.does_not_exist'));
        }

        if ($request->get('_action') !== 'updatePosition') {
            throw new BadRequestHttpException('Action does not exist');
        }

        $this->updatePosition($request->request->all(), $folder);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->getTranslator()->trans('folder.%name%.updated', [
                    '%name%' => $folder->getName(),
                ])
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        if ($request->query->has('search') && $request->get('search') != "") {
            $responseArray = [];

            $pattern = strip_tags($request->get('search'));
            $folders = $this->em()
                        ->getRepository(Folder::class)
                        ->searchBy(
                            $pattern,
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

        throw $this->createNotFoundException($this->getTranslator()->trans('no.folder.found'));
    }

    protected function updatePosition(array $parameters, Folder $folder): void
    {
        /*
         * First, we set the new parent
         */
        if (
            !empty($parameters['newParent']) &&
            is_numeric($parameters['newParent']) &&
            $parameters['newParent'] > 0
        ) {
            /** @var Folder $parent */
            $parent = $this->em()->find(Folder::class, (int) $parameters['newParent']);

            if ($parent !== null) {
                $folder->setParent($parent);
            }
        } else {
            $folder->setParent(null);
        }

        /*
         * Then compute new position
         */
        if (
            !empty($parameters['nextFolderId']) &&
            $parameters['nextFolderId'] > 0
        ) {
            /** @var Folder $nextFolder */
            $nextFolder = $this->em()->find(Folder::class, (int) $parameters['nextFolderId']);
            if ($nextFolder !== null) {
                $folder->setPosition($nextFolder->getPosition() - 0.5);
            }
        } elseif (
            !empty($parameters['prevFolderId']) &&
            $parameters['prevFolderId'] > 0
        ) {
            /** @var Folder $prevFolder */
            $prevFolder = $this->em()
                ->find(Folder::class, (int) $parameters['prevFolderId']);
            if ($prevFolder !== null) {
                $folder->setPosition($prevFolder->getPosition() + 0.5);
            }
        }
        // Apply position update before cleaning
        $this->em()->flush();

        /** @var FolderHandler $handler */
        $handler = $this->handlerFactory->getHandler($folder);
        $handler->cleanPositions();

        $this->em()->flush();
    }
}
