<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use ApiPlatform\Util\RequestAttributesExtractor;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class NodesSourcesAddHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PreviewResolverInterface $previewResolver,
        private readonly Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0]
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->isMethodCacheable()) {
            return;
        }
        if (!$response->getContent() || !$response->isSuccessful()) {
            return;
        }

        $attributes = RequestAttributesExtractor::extractAttributes($request);
        if (\count($attributes) < 1) {
            return;
        }

        if ($this->previewResolver->isPreview()) {
            return;
        }

        if ($this->security->isGranted('IS_AUTHENTICATED')) {
            return;
        }

        $resourceCacheHeaders = $attributes['cache_headers'] ?? [];
        $data = $request->attributes->get('data');

        // if the public-property is defined and not yet set; apply it to the response
        $public = $resourceCacheHeaders['public'] ?? null;
        if (null !== $public && !$response->headers->hasCacheControlDirective('public')) {
            $public ? $response->setPublic() : $response->setPrivate();
        }

        if (!$data instanceof NodesSources) {
            return;
        }

        if ($data->getNode()->getTtl() <= 0) {
            return;
        }

        if (null !== ($maxAge = $resourceCacheHeaders['max_age'] ?? $data->getNode()->getTtl()) && !$response->headers->hasCacheControlDirective('max-age')) {
            $response->setMaxAge($maxAge * 60);
        }
        // Cache-Control "s-maxage" is only relevant is resource is not marked as "private"
        if (false !== $public && null !== ($sharedMaxAge = $resourceCacheHeaders['shared_max_age'] ?? $data->getNode()->getTtl()) && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($sharedMaxAge * 60);
        }
    }
}
