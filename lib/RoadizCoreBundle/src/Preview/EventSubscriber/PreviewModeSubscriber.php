<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\EventSubscriber;

use RZ\Roadiz\CoreBundle\Preview\Exception\PreviewNotAllowedException;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PreviewModeSubscriber implements EventSubscriberInterface
{
    public const QUERY_PARAM_NAME = '_preview';

    protected PreviewResolverInterface $previewResolver;
    protected TokenStorageInterface $tokenStorage;
    protected Security $security;

    public function __construct(
        PreviewResolverInterface $previewResolver,
        TokenStorageInterface $tokenStorage,
        Security $security
    ) {
        $this->previewResolver = $previewResolver;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
            KernelEvents::CONTROLLER => ['onControllerMatched', 10],
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    /**
     * @return bool
     */
    protected function supports(): bool
    {
        return $this->previewResolver->isPreview();
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            $event->isMainRequest() &&
            $request->query->has(static::QUERY_PARAM_NAME) &&
            \in_array(
                $request->query->get(static::QUERY_PARAM_NAME, 0),
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
     * @param ControllerEvent $event
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
     *
     * @param ResponseEvent $event
     */
    public function onResponse(ResponseEvent $event): void
    {
        if ($this->supports()) {
            $response = $event->getResponse();
            $response->expire();
            $response->headers->addCacheControlDirective('no-store');
            $response->headers->add(['X-Roadiz-Preview' => true]);
            $event->setResponse($response);
        }
    }
}
