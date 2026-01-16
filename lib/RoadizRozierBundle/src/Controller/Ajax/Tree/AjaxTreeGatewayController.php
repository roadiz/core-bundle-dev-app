<?php

declare(strict_types=1);
namespace RZ\Roadiz\RozierBundle\Controller\Ajax\Tree;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AjaxFolderTreeController;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AjaxNodeTreeController;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AjaxTagTreeController;
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
        $entity = $request->query->get('url');

        $entity = match (true) {
            str_contains($entity ?? '', 'rz-admin/tags') => 'tags',
            str_contains($entity ?? '', 'rz-admin/folders') || str_contains($entity ?? '', 'rz-admin/documents') => 'folders',
            default => 'nodes',
        };

        $data = [
            'statusCode' => 200,
            'status' => 'success',
        ];

        if ($this->isGranted('ROLE_ACCESS_NODES') && $entity === 'nodes') {
            $response = $this->nodeTreeController->getTreeAction($request);
            $data += $this->extractTree($response);
        }

        if ($this->isGranted('ROLE_ACCESS_TAGS') && $entity === 'tags') {
            $response = $this->tagTreeController->getTreeAction($request);
            $data += $this->extractTree($response);
        }

        if ($this->isGranted('ROLE_ACCESS_DOCUMENTS') && $entity === 'folders') {
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
