<?php

declare(strict_types=1);
namespace RZ\Roadiz\RozierBundle\Controller\Ajax\Tree;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(
path: '/rz-admin/ajax/tree',
name: 'treeAjaxGateway',
methods: ['GET'],
format: 'json'
)]
final class AjaxTreeGatewayController extends AbstractAjaxController
{
    public function __construct(
        private readonly AjaxNodeTreeController $nodeTreeController,
        private readonly AjaxTagTreeController $tagTreeController,
        private readonly AjaxFolderTreeController $folderTreeController,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct(
            $managerRegistry,
            $serializer,
            $translator
        );
    }

    public function __invoke(Request $request): JsonResponse
    {
        $requestUrl = $request->query->get('url');

        $treeType = match (true) {
            str_contains($requestUrl ?? '', 'rz-admin/tags') => 'tags',
            str_contains($requestUrl ?? '', 'rz-admin/folders') || str_contains($requestUrl ?? '', 'rz-admin/documents') => 'folders',
            default => 'nodes',
        };

        $data = [
            'status' => 'error',
            'message' => 'You do not have the required permissions to access this tree.',
        ];

        if ($this->isGranted('ROLE_ACCESS_NODES') && $treeType === 'nodes') {
            return $this->nodeTreeController->getTreeAction($request);
        }

        if ($this->isGranted('ROLE_ACCESS_TAGS') && $treeType === 'tags') {
            return $this->tagTreeController->getTreeAction($request);
        }

        if ($this->isGranted('ROLE_ACCESS_DOCUMENTS') && $treeType === 'folders') {
            return $this->folderTreeController->getTreeAction($request);
        }

        return $this->createSerializedResponse($data);
    }
}
