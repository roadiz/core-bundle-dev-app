<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Exception\OperationNotFoundException;
use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
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
        private readonly PathResolverInterface $pathResolver,
        private readonly WebResponseDataTransformerInterface $webResponseDataTransformer,
        private readonly IriConverterInterface $iriConverter,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly ApiResourceOperationNameGenerator $apiResourceOperationNameGenerator,
    ) {
    }

    public function __invoke(?Request $request): ?WebResponseInterface
    {
        try {
            if (
                null === $request ||
                empty($request->query->get('path'))
            ) {
                throw new InvalidArgumentException('path query parameter is mandatory');
            }
            $resource = $this->normalizeResourcePath(
                $request,
                (string) $request->query->get('path')
            );
            $request->attributes->set('data', $resource);
            $request->attributes->set('id', $resource->getId());
            /*
             * Force API Platform to look for real resource configuration and serialization
             * context. You must define "%entity%_get_by_path" operation for your API resource configuration.
             */
            $resourceClass = get_class($resource);
            $operationName = $this->apiResourceOperationNameGenerator->generateGetByPath($resourceClass);

            $request->attributes->set('_api_operation_name', $operationName);
            $request->attributes->set('_api_resource_class', $resourceClass);
            $request->attributes->set('_stateless', true);
            return $this->webResponseDataTransformer->transform($resource, WebResponseInterface::class);
        } catch (ResourceNotFoundException $exception) {
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
                null !== $resource->getRedirectUri() &&
                (new UnicodeString($resource->getRedirectUri()))->startsWith('/')
            ) {
                /*
                 * Recursive call to normalize path coming from Redirection if redirected path
                 * is internal (starting with /)
                 */
                return $this->normalizeResourcePath($request, $resource->getRedirectUri());
            }
        }

        $this->addResourceToCacheTags($request, $resource);

        /*
         * Or plain entity
         */
        return $resource;
    }

    protected function addResourceToCacheTags(?Request $request, PersistableInterface $resource): void
    {
        if (null !== $request) {
            $iri = $this->iriConverter->getIriFromResource($resource);
            $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [ $iri => $iri ]);
        }
    }
}
