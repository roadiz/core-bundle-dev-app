<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CacheController extends AbstractController
{
    public function __construct(
        #[Autowire('cache.global_clearer')]
        private readonly CacheClearerInterface $cacheClearer,
        private readonly LoggerInterface $logger,
        private readonly LogTrail $logTrail,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function deleteDoctrineCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new CachePurgeRequestEvent();
            $this->eventDispatcher->dispatch($event);
            $this->cacheClearer->clear('');

            $msg = $this->translator->trans('cache.deleted');
            $this->logTrail->publishConfirmMessage($request, $msg);

            foreach ($event->getMessages() as $message) {
                $this->logger->info(sprintf('Cache cleared: %s', $message['description']));
            }
            foreach ($event->getErrors() as $message) {
                $this->logTrail->publishErrorMessage($request, sprintf('Could not clear cache: %s', $message['description']));
            }

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('adminHomePage');
        }

        return $this->render('@RoadizRozier/cache/deleteDoctrine.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function deleteAssetsCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventDispatcher->dispatch(new CachePurgeAssetsRequestEvent());
            $msg = $this->translator->trans('cache.deleted');
            $this->logTrail->publishConfirmMessage($request, $msg);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('adminHomePage');
        }

        return $this->render('@RoadizRozier/cache/deleteAssets.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
