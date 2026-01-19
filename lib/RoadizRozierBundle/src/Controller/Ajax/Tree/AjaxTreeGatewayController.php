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
            'statusCode' => 200,
            'status' => 'success',
        ];

        if ($this->isGranted('ROLE_ACCESS_NODES') && $treeType === 'nodes') {
            $response = $this->nodeTreeController->getTreeAction($request);
            $data += $this->extractTree($response);
        }

        if ($this->isGranted('ROLE_ACCESS_TAGS') && $treeType === 'tags') {
            $response = $this->tagTreeController->getTreeAction($request);
            $data += $this->extractTree($response);
        }

        if ($this->isGranted('ROLE_ACCESS_DOCUMENTS') && $treeType === 'folders') {
            $response = $this->folderTreeController->getTreeAction($request);
            $data += $this->extractTree($response);
        }

        return $this->createSerializedResponse($data);
    }

    private function extractTree(JsonResponse $response): array
    {
        return json_decode($response->getContent(), true) ?? [];
    }
}
