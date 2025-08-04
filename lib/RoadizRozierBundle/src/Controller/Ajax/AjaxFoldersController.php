<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\EntityHandler\FolderHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxFoldersController extends AbstractAjaxController
{
    public function __construct(
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
    public function editAction(Request $request, int $folderId): JsonResponse
    {
        $this->validateRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $folder = $this->managerRegistry->getRepository(Folder::class)->find((int) $folderId);

        if (null === $folder) {
            throw $this->createNotFoundException($this->translator->trans('folder.does_not_exist'));
        }

        if ('updatePosition' !== $request->get('_action')) {
            throw new BadRequestHttpException('Action does not exist');
        }

        $this->updatePosition($request->request->all(), $folder);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans('folder.%name%.updated', [
                    '%name%' => $folder->getName(),
                ]),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    public function searchAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        if ($request->query->has('search') && '' != $request->get('search')) {
            $responseArray = [];

            $pattern = strip_tags((string) $request->get('search'));
            $folders = $this->managerRegistry
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

        throw $this->createNotFoundException($this->translator->trans('no.folder.found'));
    }

    protected function updatePosition(array $parameters, Folder $folder): void
    {
        /*
         * First, we set the new parent
         */
        if (
            !empty($parameters['newParent'])
            && is_numeric($parameters['newParent'])
            && $parameters['newParent'] > 0
        ) {
            /** @var Folder $parent */
            $parent = $this->managerRegistry->getRepository(Folder::class)->find((int) $parameters['newParent']);

            if (null !== $parent) {
                $folder->setParent($parent);
            }
        } else {
            $folder->setParent(null);
        }

        /*
         * Then compute new position
         */
        if (
            !empty($parameters['nextFolderId'])
            && $parameters['nextFolderId'] > 0
        ) {
            /** @var Folder $nextFolder */
            $nextFolder = $this->managerRegistry->getRepository(Folder::class)->find((int) $parameters['nextFolderId']);
            if (null !== $nextFolder) {
                $folder->setPosition($nextFolder->getPosition() - 0.5);
            }
        } elseif (
            !empty($parameters['prevFolderId'])
            && $parameters['prevFolderId'] > 0
        ) {
            /** @var Folder $prevFolder */
            $prevFolder = $this->managerRegistry->getRepository(Folder::class)->find((int) $parameters['prevFolderId']);
            if (null !== $prevFolder) {
                $folder->setPosition($prevFolder->getPosition() + 0.5);
            }
        }
        // Apply position update before cleaning
        $this->managerRegistry->getManager()->flush();

        /** @var FolderHandler $handler */
        $handler = $this->handlerFactory->getHandler($folder);
        $handler->cleanPositions();

        $this->managerRegistry->getManager()->flush();
    }
}
