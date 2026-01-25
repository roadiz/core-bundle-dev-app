<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceOperationNameGenerator;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Routing\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;

final class GetWebResponseByPathController extends AbstractController
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly PathResolverInterface $pathResolver,
        private readonly WebResponseDataTransformerInterface $webResponseDataTransformer,
        private readonly IriConverterInterface $iriConverter,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly ApiResourceOperationNameGenerator $apiResourceOperationNameGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(?Request $request): WebResponseInterface
    {
        try {
            if (
                null === $request
                || empty($request->query->get('path'))
            ) {
                throw new InvalidArgumentException('path query parameter is mandatory');
            }
            $resource = $this->normalizeResourcePath(
                $request,
                (string) $request->query->get('path')
            );
            $request->attributes->set('id', $resource->getId());
            $request->attributes->set('path', (string) $request->query->get('path'));
            $request->attributes->set('_route_params', [
                ...$request->attributes->get('_route_params', []),
                'path' => (string) $request->query->get('path'),
            ]);

            $resourceClass = $resource::class;
            $isNodeSource = $resource instanceof NodesSources;

            try {
                /*
                 * Force API Platform to look for real resource configuration and serialization
                 * context. You must define "%entity%_get_by_path" operation for your WebResponse resource configuration.
                 * It should be generated automatically by Roadiz when you create new reachable NodeTypes.
                 */
                $operationName = $this->apiResourceOperationNameGenerator->generateGetByPath($resourceClass);
                $webResponseClass = $request->attributes->get('_api_resource_class');
                $operation = $this->resourceMetadataCollectionFactory
                    ->create($webResponseClass)
                    ->getOperation($operationName);
                /*
                 * Add shared_max_age against Node TTL to the WebResponse cache headers if resource is a NodesSources
                 */
                if ($operation instanceof HttpOperation && $isNodeSource) {
                    $operation = $operation->withCacheHeaders([
                        ...($operation->getCacheHeaders() ?? []),
                        'shared_max_age' => $resource->getNode()->getTtl() * 60,
                    ]);
                }
                $request->attributes->set('_api_operation', $operation);
                $request->attributes->set('_web_response_item_class', $resourceClass);
                $request->attributes->set('_api_operation_name', $operationName);
            } catch (OperationNotFoundException $exception) {
                // Do not fail if operation is not found
                // But warn in logs about missing operation configuration for this resource
                $this->logger->warning($exception->getMessage());
            }

            $request->attributes->set('_stateless', true);

            if ($isNodeSource) {
                $request->attributes->set('_translation', $resource->getTranslation());
                $request->attributes->set('_locale', $resource->getTranslation()->getPreferredLocale());
            }

            $data = $this->webResponseDataTransformer->transform($resource, WebResponseInterface::class);
            $request->attributes->set('data', $data);

            return $data;
        } catch (ResourceNotFoundException|ResourceClassNotFoundException $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
        }
    }

    protected function normalizeResourcePath(?Request $request, string $path): PersistableInterface
    {
        /*
         * Serve any PersistableInterface Resource by implementing
         * your PathResolver and tagging it "roadiz_core.path_resolver"
         */
        $resourceInfo = $this->pathResolver->resolvePath(
            $path,
            ['html', 'json'],
            true,
            false
        );
        $resource = $resourceInfo->getResource();

        if (null === $resource) {
            throw new ResourceNotFoundException('Cannot resolve resource path.');
        }

        /*
         * Normalize redirection
         */
        if ($resource instanceof Redirection) {
            if (null !== $nodeSource = $resource->getRedirectNodeSource()) {
                if (!$this->previewResolver->isPreview() && !$nodeSource->getNode()->isPublished()) {
                    throw new ResourceNotFoundException('Cannot resolve resource path.');
                }
                $resource = $nodeSource;
            } elseif (
                null !== $resource->getRedirectUri()
                && (new UnicodeString($resource->getRedirectUri()))->startsWith('/')
            ) {
                /*
                 * Recursive call to normalize path coming from Redirection if redirected path
                 * is internal (starting with /)
                 */
                return $this->normalizeResourcePath($request, $resource->getRedirectUri());
            }
        }

        $this->addResourceToCacheTags($request, $resource);

        // Set translation and locale to be used in Request context
        if (null !== $resourceInfo->getTranslation()) {
            $request?->attributes->set('_translation', $resourceInfo->getTranslation());
        }

        if (null !== $resourceInfo->getLocale()) {
            $request?->attributes->set('_locale', $resourceInfo->getLocale());
        }

        /*
         * Or plain entity
         */
        return $resource;
    }

    protected function addResourceToCacheTags(?Request $request, PersistableInterface $resource): void
    {
        if (null !== $request) {
            $iri = $this->iriConverter->getIriFromResource($resource);
            if (null === $iri) {
                return;
            }
            $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [$iri => $iri]);
        }
    }
}
