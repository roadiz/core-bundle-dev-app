<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

/**
 * @package Themes\Rozier\Controllers
 */
class CacheController extends RozierApp
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function deleteDoctrineCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->buildDeleteDoctrineForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new CachePurgeRequestEvent();
            $this->dispatchEvent($event);

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

        $this->assignation['cachesInfo'] = [
            'resultCache' => $this->em()->getConfiguration()->getResultCacheImpl(),
            'hydratationCache' => $this->em()->getConfiguration()->getHydrationCacheImpl(),
            'queryCache' => $this->em()->getConfiguration()->getQueryCacheImpl(),
            'metadataCache' => $this->em()->getConfiguration()->getMetadataCacheImpl(),
        ];

        foreach ($this->assignation['cachesInfo'] as $key => $value) {
            if (null !== $value) {
                $this->assignation['cachesInfo'][$key] = get_class($value);
            } else {
                $this->assignation['cachesInfo'][$key] = false;
            }
        }

        return $this->render('@RoadizRozier/cache/deleteDoctrine.html.twig', $this->assignation);
    }

    /**
     * @return FormInterface
     */
    private function buildDeleteDoctrineForm(): FormInterface
    {
        $builder = $this->createFormBuilder();

        return $builder->getForm();
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function deleteAssetsCache(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCTRINE_CACHE_DELETE');

        $form = $this->buildDeleteAssetsForm();
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

    /**
     * @return FormInterface
     */
    private function buildDeleteAssetsForm(): FormInterface
    {
        $builder = $this->createFormBuilder();

        return $builder->getForm();
    }
}
