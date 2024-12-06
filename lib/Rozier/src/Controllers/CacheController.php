<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Themes\Rozier\RozierApp;

final class CacheController extends RozierApp
{
    public function __construct(
        private readonly CacheClearerInterface $cacheClearer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function deleteDoctrineCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new CachePurgeRequestEvent();
            $this->dispatchEvent($event);
            $this->cacheClearer->clear('');

            $msg = $this->getTranslator()->trans('cache.deleted');
            $this->publishConfirmMessage($request, $msg);

            foreach ($event->getMessages() as $message) {
                $this->logger->info(sprintf('Cache cleared: %s', $message['description']));
            }
            foreach ($event->getErrors() as $message) {
                $this->publishErrorMessage($request, sprintf('Could not clear cache: %s', $message['description']));
            }

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('adminHomePage');
        }
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/cache/deleteDoctrine.html.twig', $this->assignation);
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function deleteAssetsCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatchEvent(new CachePurgeAssetsRequestEvent());
            $msg = $this->getTranslator()->trans('cache.deleted');
            $this->publishConfirmMessage($request, $msg);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('adminHomePage');
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/cache/deleteAssets.html.twig', $this->assignation);
    }
}
