<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostCreatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasUpdatedEvent;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Exception\NoTranslationAvailableException;
use RZ\Roadiz\CoreBundle\Form\UrlAliasType;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\Forms\NodeSource\NodeSourceSeoType;
use Themes\Rozier\Forms\RedirectionType;

final class SeoController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly ManagerRegistry $managerRegistry,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function editAliasesAction(
        Request $request,
        Node $nodeId,
        ?Translation $translationId = null,
    ): Response {
        if (null === $translationId) {
            $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        } else {
            $translation = $translationId;
        }

        if (null === $translation) {
            throw new ResourceNotFoundException();
        }

        $node = $nodeId;
        /** @var NodesSources|false $source */
        $source = $nodeId->getNodeSourcesByTranslation($translation)->first();

        if (false === $source) {
            throw new ResourceNotFoundException();
        }
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $source);

        $redirections = $this->managerRegistry
            ->getRepository(Redirection::class)
            ->findBy([
                'redirectNodeSource' => $node->getNodeSources()->toArray(),
            ]);
        $uas = $this->managerRegistry
                    ->getRepository(UrlAlias::class)
                    ->findAllFromNode($node->getId());
        $availableTranslations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findAvailableTranslationsForNode($node);

        $assignation = [];
        $assignation['node'] = $node;
        $assignation['source'] = $source;
        $assignation['aliases'] = [];
        $assignation['redirections'] = [];
        $assignation['translation'] = $translation;
        $assignation['available_translations'] = $availableTranslations;

        /*
         * SEO Form
         */
        $seoForm = $this->createForm(NodeSourceSeoType::class, $source);
        $seoForm->handleRequest($request);
        if ($seoForm->isSubmitted() && $seoForm->isValid()) {
            $this->managerRegistry->getManagerForClass(NodesSources::class)->flush();
            $msg = $this->translator->trans('node.seo.updated');
            $this->logTrail->publishConfirmMessage($request, $msg, $source);
            /*
             * Dispatch event
             */
            $this->eventDispatcher->dispatch(new NodesSourcesUpdatedEvent($source));

            return $this->redirectToRoute(
                'nodesEditSEOPage',
                ['nodeId' => $node->getId(), 'translationId' => $translationId]
            );
        }

        if (null !== $response = $this->handleAddRedirection($source, $request, $assignation)) {
            return $response;
        }
        /*
         * each url alias edit form
         */
        /** @var UrlAlias $alias */
        foreach ($uas as $alias) {
            if (null !== $response = $this->handleSingleUrlAlias($alias, $request, $assignation)) {
                return $response;
            }
        }

        /** @var Redirection $redirection */
        foreach ($redirections as $redirection) {
            if (null !== $response = $this->handleSingleRedirection($redirection, $request, $assignation)) {
                return $response;
            }
        }

        /*
         * Main ADD url alias form
         */
        $alias = new UrlAlias();
        $addAliasForm = $this->formFactory->createNamed(
            'add_urlalias_'.$node->getId(),
            UrlAliasType::class,
            $alias,
            [
                'with_translation' => true,
            ]
        );
        $addAliasForm->handleRequest($request);
        if ($addAliasForm->isSubmitted() && $addAliasForm->isValid()) {
            try {
                $alias = $this->addNodeUrlAlias($alias, $node, $addAliasForm->get('translation')->getData());
                $msg = $this->translator->trans('url_alias.%alias%.created.%translation%', [
                    '%alias%' => $alias->getAlias(),
                    '%translation%' => $alias->getNodeSource()->getTranslation()->getName(),
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $source);
                /*
                 * Dispatch event
                 */
                $this->eventDispatcher->dispatch(new UrlAliasCreatedEvent($alias));

                return $this->redirect($this->generateUrl(
                    'nodesEditSEOPage',
                    ['nodeId' => $node->getId(), 'translationId' => $translationId]
                ).'#manage-aliases');
            } catch (EntityAlreadyExistsException $e) {
                $addAliasForm->addError(new FormError($e->getMessage()));
            } catch (NoTranslationAvailableException $e) {
                $addAliasForm->addError(new FormError($e->getMessage()));
            }
        }

        $assignation['form'] = $addAliasForm->createView();
        if ($source->isReachable()) {
            $assignation['seoForm'] = $seoForm->createView();
        }

        return $this->render('@RoadizRozier/nodes/editAliases.html.twig', $assignation);
    }

    private function addNodeUrlAlias(UrlAlias $alias, Node $node, Translation $translation): UrlAlias
    {
        $entityManager = $this->managerRegistry->getManagerForClass(UrlAlias::class);
        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->managerRegistry
                           ->getRepository(NodesSources::class)
                           ->setDisplayingAllNodesStatuses(true)
                           ->setDisplayingNotPublishedNodes(true)
                           ->findOneBy(['node' => $node, 'translation' => $translation]);

        if (null !== $nodeSource) {
            $alias->setNodeSource($nodeSource);
            $entityManager->persist($alias);
            $entityManager->flush();

            return $alias;
        } else {
            $msg = $this->translator->trans('url_alias.no_translation.%translation%', [
                '%translation%' => $translation->getName(),
            ]);
            throw new NoTranslationAvailableException($msg);
        }
    }

    private function handleSingleUrlAlias(
        UrlAlias $alias,
        Request $request,
        array &$assignation,
    ): ?RedirectResponse {
        $entityManager = $this->managerRegistry->getManagerForClass(UrlAlias::class);
        $editForm = $this->formFactory->createNamed(
            'edit_urlalias_'.$alias->getId(),
            UrlAliasType::class,
            $alias
        );
        $deleteForm = $this->formFactory->createNamed('delete_urlalias_'.$alias->getId());
        // Match edit
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $entityManager->flush();
                $msg = $this->translator->trans(
                    'url_alias.%alias%.updated',
                    ['%alias%' => $alias->getAlias()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $alias->getNodeSource());
                /*
                 * Dispatch event
                 */
                $this->eventDispatcher->dispatch(new UrlAliasUpdatedEvent($alias));
                /** @var Translation $translation */
                $translation = $alias->getNodeSource()->getTranslation();

                return $this->redirect($this->generateUrl(
                    'nodesEditSEOPage',
                    [
                        'nodeId' => $alias->getNodeSource()->getNode()->getId(),
                        'translationId' => $translation->getId(),
                    ]
                ).'#manage-aliases');
            } catch (EntityAlreadyExistsException $e) {
                $editForm->addError(new FormError($e->getMessage()));
            } catch (\RuntimeException $exception) {
                $editForm->addError(new FormError($exception->getMessage()));
            }
        }

        // Match delete
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($alias);
            $entityManager->flush();
            $msg = $this->translator->trans('url_alias.%alias%.deleted', ['%alias%' => $alias->getAlias()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $alias->getNodeSource());

            /*
             * Dispatch event
             */
            $this->eventDispatcher->dispatch(new UrlAliasDeletedEvent($alias));

            /** @var Translation $translation */
            $translation = $alias->getNodeSource()->getTranslation();

            return $this->redirect($this->generateUrl(
                'nodesEditSEOPage',
                [
                    'nodeId' => $alias->getNodeSource()->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            ).'#manage-aliases');
        }

        $assignation['aliases'][] = [
            'alias' => $alias,
            'editForm' => $editForm->createView(),
            'deleteForm' => $deleteForm->createView(),
        ];

        return null;
    }

    private function handleAddRedirection(
        NodesSources $source,
        Request $request,
        array &$assignation,
    ): ?RedirectResponse {
        $entityManager = $this->managerRegistry->getManagerForClass(Redirection::class);
        $redirection = new Redirection();
        $redirection->setRedirectNodeSource($source);
        $redirection->setType(Response::HTTP_MOVED_PERMANENTLY);

        $addForm = $this->formFactory->createNamed(
            'add_redirection',
            RedirectionType::class,
            $redirection,
            [
                'placeholder' => $this->generateUrl(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [RouteObjectInterface::ROUTE_OBJECT => $source]
                ),
                'only_query' => true,
            ]
        );

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $entityManager->persist($redirection);
            $entityManager->flush();
            $this->eventDispatcher->dispatch(new PostCreatedRedirectionEvent($redirection));

            /** @var Translation $translation */
            $translation = $redirection->getRedirectNodeSource()->getTranslation();

            return $this->redirect($this->generateUrl(
                'nodesEditSEOPage',
                [
                    'nodeId' => $redirection->getRedirectNodeSource()->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            ).'#manage-redirections');
        }

        if ($source->isReachable()) {
            $assignation['addRedirection'] = $addForm->createView();
        }

        return null;
    }

    private function handleSingleRedirection(
        Redirection $redirection,
        Request $request,
        array &$assignation,
    ): ?RedirectResponse {
        $entityManager = $this->managerRegistry->getManagerForClass(Redirection::class);
        $editForm = $this->formFactory->createNamed(
            'edit_redirection_'.$redirection->getId(),
            RedirectionType::class,
            $redirection,
            [
                'only_query' => true,
            ]
        );

        /** @var Translation $translation */
        $translation = $redirection->getRedirectNodeSource()->getTranslation();
        $deleteForm = $this->formFactory->createNamed('delete_redirection_'.$redirection->getId());

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();
            $this->eventDispatcher->dispatch(new PostUpdatedRedirectionEvent($redirection));

            return $this->redirect($this->generateUrl(
                'nodesEditSEOPage',
                [
                    'nodeId' => $redirection->getRedirectNodeSource()->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            ).'#manage-redirections');
        }

        // Match delete
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($redirection);
            $entityManager->flush();
            $this->eventDispatcher->dispatch(new PostCreatedRedirectionEvent($redirection));

            return $this->redirect($this->generateUrl(
                'nodesEditSEOPage',
                [
                    'nodeId' => $redirection->getRedirectNodeSource()->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            ).'#manage-redirections');
        }
        $assignation['redirections'][] = [
            'redirection' => $redirection,
            'editForm' => $editForm->createView(),
            'deleteForm' => $deleteForm->createView(),
        ];

        return null;
    }
}
