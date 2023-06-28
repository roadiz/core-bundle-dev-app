<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Routing\NodeRouter;
use RZ\Roadiz\CoreBundle\TwigExtension\JwtExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Themes\Rozier\Forms\NodeSource\NodeSourceType;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Traits\VersionedControllerTrait;
use Twig\Error\RuntimeError;

class NodesSourcesController extends RozierApp
{
    use VersionedControllerTrait;

    private JwtExtension $jwtExtension;
    private FormErrorSerializer $formErrorSerializer;

    public function __construct(JwtExtension $jwtExtension, FormErrorSerializer $formErrorSerializer)
    {
        $this->jwtExtension = $jwtExtension;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Return an edition form for requested node.
     *
     * @param Request $request
     * @param int     $nodeId
     * @param int     $translationId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editSourceAction(Request $request, int $nodeId, int $translationId): Response
    {
        $this->validateNodeAccessForRole('ROLE_ACCESS_NODES', $nodeId);

        /** @var Translation|null $translation */
        $translation = $this->em()->find(Translation::class, $translationId);

        if (null === $translation) {
            throw new ResourceNotFoundException('Translation does not exist');
        }
        /*
         * Here we need to directly select nodeSource
         * if not doctrine will grab a cache tag because of NodeTreeWidget
         * that is initialized before calling route method.
         */
        /** @var Node|null $gNode */
        $gNode = $this->em()->find(Node::class, $nodeId);
        if (null === $gNode) {
            throw new ResourceNotFoundException('Node does not exist');
        }

        /** @var NodesSources|null $source */
        $source = $this->em()
                       ->getRepository(NodesSources::class)
                       ->setDisplayingAllNodesStatuses(true)
                       ->setDisplayingNotPublishedNodes(true)
                       ->findOneBy(['translation' => $translation, 'node' => $gNode]);

        if (null === $source) {
            throw new ResourceNotFoundException('Node source does not exist');
        }

        $this->em()->refresh($source);

        $node = $source->getNode();

        /**
         * Versioning
         */
        if ($this->isGranted('ROLE_ACCESS_VERSIONS')) {
            if (null !== $response = $this->handleVersions($request, $source)) {
                return $response;
            }
        }

        $form = $this->createForm(
            NodeSourceType::class,
            $source,
            [
                'class' => $node->getNodeType()->getSourceEntityFullQualifiedClassName(),
                'nodeType' => $node->getNodeType(),
                'withVirtual' => true,
                'withTitle' => true,
                'disabled' => $this->isReadOnly,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid() && !$this->isReadOnly) {
                $this->onPostUpdate($source, $request);

                if (!$request->isXmlHttpRequest()) {
                    return $this->getPostUpdateRedirection($source);
                }

                $jwtToken = $this->jwtExtension->createPreviewJwt();

                if ($this->getSettingsBag()->get('custom_preview_scheme')) {
                    $previewUrl = $this->generateUrl($source, [
                        'canonicalScheme' => $this->getSettingsBag()->get('custom_preview_scheme'),
                        'token' => $jwtToken,
                        NodeRouter::NO_CACHE_PARAMETER => true
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                } elseif ($this->getSettingsBag()->get('custom_public_scheme')) {
                    $previewUrl = $this->generateUrl($source, [
                        'canonicalScheme' => $this->getSettingsBag()->get('custom_public_scheme'),
                        '_preview' => 1,
                        'token' => $jwtToken,
                        NodeRouter::NO_CACHE_PARAMETER => true
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $previewUrl = $this->generateUrl($source, [
                        '_preview' => 1,
                        'token' => $jwtToken,
                        NodeRouter::NO_CACHE_PARAMETER => true
                    ]);
                }

                if ($this->getSettingsBag()->get('custom_public_scheme')) {
                    $publicUrl = $this->generateUrl($source, [
                        'canonicalScheme' => $this->getSettingsBag()->get('custom_public_scheme'),
                        NodeRouter::NO_CACHE_PARAMETER => true
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $publicUrl = $this->generateUrl($source, [
                        NodeRouter::NO_CACHE_PARAMETER => true
                    ]);
                }

                return new JsonResponse([
                    'status' => 'success',
                    'public_url' => $source->getNode()->isPublished() ? $publicUrl : null,
                    'preview_url' => $previewUrl,
                    'errors' => [],
                ], Response::HTTP_PARTIAL_CONTENT);
            }

            if ($this->isReadOnly) {
                $form->addError(new FormError('nodeSource.form.is_read_only'));
            }

            /*
             * Handle errors when Ajax POST requests
             */
            if ($request->isXmlHttpRequest()) {
                $errors = $this->formErrorSerializer->getErrorsAsArray($form);
                return new JsonResponse([
                    'status' => 'fail',
                    'errors' => $errors,
                    'message' => $this->getTranslator()->trans('form_has_errors.check_you_fields'),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $availableTranslations = $this->em()
            ->getRepository(Translation::class)
            ->findAvailableTranslationsForNode($gNode);

        $this->assignation['translation'] = $translation;
        $this->assignation['available_translations'] = $availableTranslations;
        $this->assignation['node'] = $node;
        $this->assignation['source'] = $source;
        $this->assignation['form'] = $form->createView();
        $this->assignation['readOnly'] = $this->isReadOnly;

        return $this->render('@RoadizRozier/nodes/editSource.html.twig', $this->assignation);
    }

    /**
     * Return a remove form for requested nodeSource.
     *
     * @param Request $request
     * @param int     $nodeSourceId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function removeAction(Request $request, int $nodeSourceId): Response
    {
        /** @var NodesSources|null $ns */
        $ns = $this->em()->find(NodesSources::class, $nodeSourceId);
        if (null === $ns) {
            throw new ResourceNotFoundException('Node source does not exist');
        }
        /** @var Node $node */
        $node = $ns->getNode();
        $this->em()->refresh($ns->getNode());

        $this->validateNodeAccessForRole('ROLE_ACCESS_NODES_DELETE', $node->getId());

        /*
         * Prevent deleting last node-source available in node.
         */
        if ($node->getNodeSources()->count() <= 1) {
            $msg = $this->getTranslator()->trans('node_source.%node_source%.%translation%.cant.deleted', [
                '%node_source%' => $node->getNodeName(),
                '%translation%' => $ns->getTranslation()->getName(),
            ]);

            throw new BadRequestHttpException($msg);
        }

        $builder = $this->createFormBuilder()
                        ->add('nodeId', HiddenType::class, [
                            'data' => $nodeSourceId,
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Node $node */
            $node = $ns->getNode();
            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodesSourcesDeletedEvent($ns));

            $this->em()->remove($ns);
            $this->em()->flush();

            $ns = $node->getNodeSources()->first() ?: null;

            if (null === $ns) {
                throw new ResourceNotFoundException('No more node-source available for this node.');
            }

            $msg = $this->getTranslator()->trans('node_source.%node_source%.deleted.%translation%', [
                '%node_source%' => $node->getNodeName(),
                '%translation%' => $ns->getTranslation()->getName(),
            ]);

            $this->publishConfirmMessage($request, $msg, $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                ['nodeId' => $node->getId(), "translationId" => $ns->getTranslation()->getId()]
            );
        }

        $this->assignation["nodeSource"] = $ns;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/deleteSource.html.twig', $this->assignation);
    }

    protected function onPostUpdate(PersistableInterface $entity, Request $request): void
    {
        /*
         * Dispatch pre-flush event
         */
        if (!$entity instanceof NodesSources) {
            return;
        }

        $this->dispatchEvent(new NodesSourcesPreUpdatedEvent($entity));
        $this->em()->flush();
        $this->dispatchEvent(new NodesSourcesUpdatedEvent($entity));

        $msg = $this->getTranslator()->trans('node_source.%node_source%.updated.%translation%', [
            '%node_source%' => $entity->getNode()->getNodeName(),
            '%translation%' => $entity->getTranslation()->getName(),
        ]);

        $this->publishConfirmMessage($request, $msg, $entity);
    }

    protected function getPostUpdateRedirection(PersistableInterface $entity): ?Response
    {
        if (!$entity instanceof NodesSources) {
            return null;
        }

        /** @var Translation $translation */
        $translation = $entity->getTranslation();
        return $this->redirectToRoute(
            'nodesEditSourcePage',
            [
                'nodeId' => $entity->getNode()->getId(),
                'translationId' => $translation->getId()
            ]
        );
    }
}
