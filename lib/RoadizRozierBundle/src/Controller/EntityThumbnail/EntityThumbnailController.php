<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\EntityThumbnail;

use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Controller to fetch entity thumbnail information via API.
 */
final class EntityThumbnailController extends AbstractController
{
    public function __construct(
        private readonly EntityThumbnailService $entityThumbnailService,
        private readonly SerializerInterface $serializer,
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

        if (!class_exists((string) $entityClass)) {
            throw new BadRequestHttpException('Invalid entity class');
        }

        // Get thumbnail data - provider is responsible for fetching entity
        $thumbnail = $this->entityThumbnailService->getThumbnail((string) $entityClass, $entityId);

        if (null === $thumbnail) {
            $thumbnail = new EntityThumbnail();
        }

        return new JsonResponse(
            $this->serializer->serialize($thumbnail, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
