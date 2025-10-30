<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\EntityThumbnail;

use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller to fetch entity thumbnail information via API.
 */
final class EntityThumbnailController extends AbstractController
{
    public function __construct(
        private readonly EntityThumbnailService $entityThumbnailService,
    ) {
    }

    #[Route('/rz-admin/ajax/entity-thumbnail', name: 'entityThumbnailAction', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $entityClass = $request->query->get('class');
        $entityId = $request->query->get('id');

        if (empty($entityClass) || empty($entityId)) {
            throw new BadRequestHttpException('Both "class" and "id" parameters are required');
        }

        // Validate and normalize entity class name
        $entityClass = $this->normalizeEntityClass((string) $entityClass);

        if (!class_exists($entityClass)) {
            throw new BadRequestHttpException('Invalid entity class');
        }

        // Get thumbnail data - provider is responsible for fetching entity
        $thumbnailData = $this->entityThumbnailService->getThumbnail($entityClass, $entityId);

        if (null === $thumbnailData) {
            return new JsonResponse([
                'url' => null,
                'alt' => null,
                'title' => null,
            ], Response::HTTP_OK);
        }

        return new JsonResponse($thumbnailData, Response::HTTP_OK);
    }

    /**
     * Normalize entity class name to prevent directory traversal and ensure valid class names.
     */
    private function normalizeEntityClass(string $entityClass): string
    {
        // Allow short names like 'User', 'Document', 'NodesSources'
        // and convert them to fully qualified class names
        $classMap = [
            'User' => \RZ\Roadiz\CoreBundle\Entity\User::class,
            'Document' => \RZ\Roadiz\CoreBundle\Entity\Document::class,
            'NodesSources' => \RZ\Roadiz\CoreBundle\Entity\NodesSources::class,
            'Node' => \RZ\Roadiz\CoreBundle\Entity\Node::class,
            'Tag' => \RZ\Roadiz\CoreBundle\Entity\Tag::class,
        ];

        // If it's a short name, convert it
        if (isset($classMap[$entityClass])) {
            return $classMap[$entityClass];
        }

        // If it's already a fully qualified class name, validate it
        if (str_starts_with($entityClass, 'RZ\\Roadiz\\')) {
            return $entityClass;
        }

        throw new BadRequestHttpException('Invalid entity class name');
    }
}
