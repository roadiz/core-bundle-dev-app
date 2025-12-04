<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\EntityThumbnail;

use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnailProviderInterface;
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
        private readonly EntityThumbnailProviderInterface $entityThumbnailProvider,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route(
        path: '/rz-admin/ajax/entity-thumbnail',
        name: 'ajaxEntityThumbnail',
        methods: ['GET'],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $entityClass = $request->query->get('class');
        $entityId = $request->query->get('id');

        if (!is_string($entityClass)) {
            throw new BadRequestHttpException('"class" parameters must be a valid class name');
        }

        if (empty($entityClass) || empty($entityId)) {
            throw new BadRequestHttpException('Both "class" and "id" parameters are required');
        }

        if (!is_string($entityId) && !(is_int($entityId) && $entityId > 0)) {
            throw new BadRequestHttpException('"id" parameters must be a valid identifier');
        }

        if (!class_exists($entityClass)) {
            throw new BadRequestHttpException('Invalid entity class');
        }

        // Get thumbnail data - provider is responsible for fetching entity
        $thumbnail = $this->entityThumbnailProvider->getThumbnail($entityClass, $entityId);

        if (null === $thumbnail) {
            $thumbnail = new EntityThumbnail();
        }

        $response = new JsonResponse(
            $this->serializer->serialize($thumbnail, 'json'),
            Response::HTTP_OK,
            [],
            true
        );

        /*
         * Only set ETag if we have a URL to avoid caching empty responses
         */
        if (null !== $thumbnail->url) {
            $response->setEtag(md5($response->getContent() ?: ''));
            $response->isNotModified($request);
        }

        /*
         * Private cache for 10 minutes
         */
        $response->setPrivate();
        $response->setMaxAge(600);

        return $response;
    }
}
