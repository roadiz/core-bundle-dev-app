<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SignatureSubscriber implements EventSubscriberInterface
{
    private string $version;
    private bool $debug;
    private bool $hideRoadizVersion;

    public function __construct(string $cmsVersion, bool $hideRoadizVersion, bool $debug = false)
    {
        $this->version = $cmsVersion;
        $this->debug = $debug;
        $this->hideRoadizVersion = $hideRoadizVersion;
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

        if ($this->debug && $this->version) {
            $response->headers->add(['X-Version' => $this->version]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
