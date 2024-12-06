<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Link\EvolvableLinkProviderInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

final readonly class NodesSourcesLinkHeaderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvent::class => ['onKernelView', 15],
        ];
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $resources = $request->attributes->get('data');
        $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());

        // Work with WebResponse item instead of WebResponse itself
        if ($resources instanceof WebResponseInterface) {
            $resources = $resources->getItem();
        }

        if (!$resources instanceof NodesSources || !$linkProvider instanceof EvolvableLinkProviderInterface) {
            return;
        }

        /*
         * Preview and authentication is handled at repository level.
         */
        /** @var NodesSources[] $allSources */
        $allSources = $this->managerRegistry
            ->getRepository(get_class($resources))
            ->findByNode($resources->getNode());

        foreach ($allSources as $singleSource) {
            $linkProvider = $linkProvider->withLink(
                (new Link(
                    'alternate',
                    $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                        RouteObjectInterface::ROUTE_OBJECT => $singleSource,
                    ])
                ))
                    ->withAttribute('hreflang', $singleSource->getTranslation()->getLocale())
                    // Must encode translation name in base64 because headers are ASCII only
                    ->withAttribute('title', \base64_encode($singleSource->getTranslation()->getName()))
                    ->withAttribute('type', 'text/html')
            );
        }
        $request->attributes->set('_links', $linkProvider);
    }
}
