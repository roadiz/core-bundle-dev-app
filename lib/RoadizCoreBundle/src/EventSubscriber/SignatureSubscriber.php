<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SignatureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $cmsVersion,
        private readonly bool $hideRoadizVersion,
        private readonly bool $debug = false,
    ) {
    }

    /**
     * Filters the Response.
     *
     * @param ResponseEvent $event A ResponseEvent instance
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || $this->hideRoadizVersion) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->add(['X-Powered-By' => 'Roadiz CMS']);

        if ($this->debug && $this->cmsVersion) {
            $response->headers->add(['X-Version' => $this->cmsVersion]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
