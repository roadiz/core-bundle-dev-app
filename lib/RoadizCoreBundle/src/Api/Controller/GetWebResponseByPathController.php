<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Routing\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;

final class GetWebResponseByPathController extends AbstractController
{
    private RequestStack $requestStack;
    private PathResolverInterface $pathResolver;
    private WebResponseDataTransformerInterface $webResponseDataTransformer;
    private IriConverterInterface $iriConverter;

    /**
     * @param RequestStack $requestStack
     * @param PathResolverInterface $pathResolver
     * @param WebResponseDataTransformerInterface $webResponseDataTransformer
     * @param IriConverterInterface $iriConverter
     */
    public function __construct(
        RequestStack $requestStack,
        PathResolverInterface $pathResolver,
        WebResponseDataTransformerInterface $webResponseDataTransformer,
        IriConverterInterface $iriConverter
    ) {
        $this->requestStack = $requestStack;
        $this->pathResolver = $pathResolver;
        $this->webResponseDataTransformer = $webResponseDataTransformer;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(): ?WebResponseInterface
    {
        try {
            if (
                null === $this->requestStack->getMainRequest() ||
                empty($this->requestStack->getMainRequest()->query->get('path'))
            ) {
                throw new InvalidArgumentException('path query parameter is mandatory');
            }
            $resource = $this->normalizeResourcePath(
                (string) $this->requestStack->getMainRequest()->query->get('path')
            );
            if (null === $resource) {
                throw new ResourceNotFoundException('Resource not found');
            }
            $this->requestStack->getMainRequest()->attributes->set('data', $resource);
            $this->requestStack->getMainRequest()->attributes->set('id', $resource->getId());
            /*
             * Force API Platform to look for real resource configuration and serialization
             * context. You must define "itemOperations.getByPath" for your API resource configuration.
             */
            $this->requestStack->getMainRequest()->attributes->set('_api_resource_class', get_class($resource));
            return $this->webResponseDataTransformer->transform($resource, WebResponseInterface::class);
        } catch (ResourceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }
    }

    /**
     * @param string $path
     * @return PersistableInterface|null
     */
    protected function normalizeResourcePath(string $path): ?PersistableInterface
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

        /*
         * Normalize redirection
         */
        if ($resource instanceof Redirection) {
            if (null !== $resource->getRedirectNodeSource()) {
                $resource = $resource->getRedirectNodeSource();
            } elseif (
                null !== $resource->getRedirectUri() &&
                (new UnicodeString($resource->getRedirectUri()))->startsWith('/')
            ) {
                /*
                 * Recursive call to normalize path coming from Redirection if redirected path
                 * is internal (starting with /)
                 */
                return $this->normalizeResourcePath($resource->getRedirectUri());
            }
        }

        $this->addResourceToCacheTags($resource);

        /*
         * Or plain entity
         */
        return $resource;
    }

    protected function addResourceToCacheTags(?PersistableInterface $resource): void
    {
        $request = $this->requestStack->getMainRequest();
        if (null !== $request && null !== $resource) {
            $iri = $this->iriConverter->getIriFromItem($resource);
            $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [$iri]);
        }
    }
}
