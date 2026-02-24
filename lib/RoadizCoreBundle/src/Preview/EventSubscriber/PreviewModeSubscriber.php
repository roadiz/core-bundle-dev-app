<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\EventSubscriber;

use RZ\Roadiz\CoreBundle\Preview\Exception\PreviewNotAllowedException;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PreviewModeSubscriber implements EventSubscriberInterface
{
    public const QUERY_PARAM_NAME = '_preview';

    public function __construct(
        private readonly PreviewResolverInterface $previewResolver,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 2047],
            KernelEvents::CONTROLLER => ['onControllerMatched', 10],
            // Must Triggered after API platform AddHeadersListener
            KernelEvents::RESPONSE => ['onResponse', -255],
        ];
    }

    private function supports(): bool
    {
        return $this->previewResolver->isPreview();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            $event->isMainRequest()
            && $request->query->has(self::QUERY_PARAM_NAME)
            && \in_array(
                $request->query->get(self::QUERY_PARAM_NAME, 0),
                ['true', true, '1', 1, 'on', 'yes', 'y'],
                true
            )
        ) {
            $request->attributes->set('preview', true);
        }
    }

    /**
     * Preview mode security enforcement.
     * You MUST check here is user can use preview mode BEFORE going
     * any further into your app logic.
     *
     * @throws PreviewNotAllowedException
     */
    public function onControllerMatched(ControllerEvent $event): void
    {
        if ($this->supports() && $event->isMainRequest()) {
            if (!$this->security->isGranted($this->previewResolver->getRequiredRole())) {
                throw new PreviewNotAllowedException('You are not granted to use preview mode.');
            }
        }
    }

    /**
     * Enforce cache disabling.
     */
    public function onResponse(ResponseEvent $event): void
    {
        if ($this->supports()) {
            $response = $event->getResponse();
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-store');
            $response->headers->add(['X-Roadiz-Preview' => true]);
            $response->setPrivate();
        }
    }
}
