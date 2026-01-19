<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\VersionedControllerTrait;
use RZ\Roadiz\RozierBundle\Form\NodeSource\NodeSourceType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class NodesSourcesController extends AbstractController
{
    use VersionedControllerTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly FormErrorSerializer $formErrorSerializer,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $formFactory,
        private readonly LogTrail $logTrail,
        private readonly TranslationRepository $translationRepository,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
    }

    #[\Override]
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    #[\Override]
    protected function createNamedFormBuilder(string $name = 'form', mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, $data, $options);
    }

    #[Route(
        path: '/rz-admin/nodes/source/edit/{nodeSourceId}',
        name: 'nodesForwardSourcePage',
        requirements: [
            'nodeSourceId' => '[0-9]+',
        ],
    )]
    public function redirectEditSourceAction(
        Request $request,
        #[MapEntity(expr: 'repository.find(nodeSourceId)')]
        NodesSources $nodesSources,
    ): Response {
        return $this->redirectToRoute(
            'nodesEditSourcePage',
            [
                'nodeId' => $nodesSources->getNode()->getId(),
                'translationId' => $nodesSources->getTranslation()->getId(),
            ]
        );
    }

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/source/{translationId}',
        name: 'nodesEditSourcePage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
        ],
    )]
    public function editSourceAction(
        Request $request,
        #[MapEntity(
            // Need to disable status filtering to get node sources in any status because we
            // use repository findOneBy method which is affected by status filtering.
            expr: 'repository.setDisplayingAllNodesStatuses(true).findOneBy({"translation": translationId, "node": nodeId})',
            evictCache: true,
            message: 'Node source does not exist',
        )]
        NodesSources $source,
    ): Response {
        $node = $source->getNode();
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $source->getNode());

        $assignation = [];
        /*
         * Versioning
         */
        if ($this->isGranted('ROLE_ACCESS_VERSIONS')) {
            if (null !== $response = $this->handleVersions($request, $source, $assignation)) {
                return $response;
            }
        }
        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if (null === $nodeType) {
            throw new ResourceNotFoundException('Node type does not exist');
        }

        $form = $this->createForm(
            NodeSourceType::class,
            $source,
            [
                'class' => $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType),
                'nodeType' => $nodeType,
                'withVirtual' => true,
                'withTitle' => true,
                'disabled' => $this->isReadOnly,
            ]
        );
        $form->handleRequest($request);
        $isJsonRequest =
            $request->isXmlHttpRequest()
            || \in_array('application/json', $request->getAcceptableContentTypes())
        ;

        if ($form->isSubmitted()) {
            if ($form->isValid() && !$this->isReadOnly) {
                $this->onPostUpdate($source, $request);

                if (!$isJsonRequest) {
                    return $this->getPostUpdateRedirection($source);
                }

                return new JsonResponse([
                    'status' => 'success',
                    'errors' => [],
                ], Response::HTTP_OK);
            }

            if ($this->isReadOnly) {
                $form->addError(new FormError('nodeSource.form.is_read_only'));
            }

            /*
             * Handle errors when Ajax POST requests
             */
            if ($isJsonRequest) {
                $errors = $this->formErrorSerializer->getErrorsAsArray($form);

                return new JsonResponse([
                    'status' => 'fail',
                    'errors' => $errors,
                    'message' => $this->translator->trans('form_has_errors.check_you_fields'),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $availableTranslations = $this->translationRepository->findAvailableTranslationsForNode($node);

        return $this->render('@RoadizRozier/nodes/editSource.html.twig', [
            ...$assignation,
            'translation' => $source->getTranslation(),
            'node' => $node,
            'source' => $source,
            'form' => $form->createView(),
            'readOnly' => $this->isReadOnly,
            'available_translations' => $availableTranslations,
        ]);
    }

    /**
     * Return a remove form for requested nodeSource.
     *
     * @throws RuntimeError
     */
    #[Route(
        path: '/rz-admin/nodes/source/delete/{nodeSourceId}',
        name: 'nodesDeleteSourcePage',
        requirements: [
            'nodeSourceId' => '[0-9]+',
        ],
    )]
    public function removeAction(
        Request $request,
        #[MapEntity(expr: 'repository.find(nodeSourceId)', message: 'Node source does not exist')]
        NodesSources $ns,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $ns);
        $node = $ns->getNode();
        $manager = $this->managerRegistry->getManager();
        $manager->refresh($ns->getNode());

        /*
         * Prevent deleting last node-source available in node.
         */
        if ($node->getNodeSources()->count() <= 1) {
            $msg = $this->translator->trans('node_source.%node_source%.%translation%.cant.deleted', [
                '%node_source%' => $node->getNodeName(),
                '%translation%' => $ns->getTranslation()->getName(),
            ]);

            throw new BadRequestHttpException($msg);
        }

        $builder = $this->createFormBuilder()
                        ->add('nodeId', HiddenType::class, [
                            'data' => $ns->getId(),
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $node = $ns->getNode();

            $this->eventDispatcher->dispatch(new NodesSourcesDeletedEvent($ns));

            $manager->remove($ns);
            $manager->flush();

            $ns = $node->getNodeSources()->first() ?: null;

            if (null === $ns) {
                throw new ResourceNotFoundException('No more node-source available for this node.');
            }

            $msg = $this->translator->trans('node_source.%node_source%.deleted.%translation%', [
                '%node_source%' => $node->getNodeName(),
                '%translation%' => $ns->getTranslation()->getName(),
            ]);

            $this->logTrail->publishConfirmMessage($request, $msg, $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                ['nodeId' => $node->getId(), 'translationId' => $ns->getTranslation()->getId()]
            );
        }

        return $this->render('@RoadizRozier/nodes/deleteSource.html.twig', [
            'nodeSource' => $ns,
            'form' => $form->createView(),
        ]);
    }

    #[\Override]
    protected function onPostUpdate(PersistableInterface $entity, Request $request): void
    {
        /*
         * Dispatch pre-flush event
         */
        if (!$entity instanceof NodesSources) {
            return;
        }

        $this->eventDispatcher->dispatch(new NodesSourcesPreUpdatedEvent($entity));
        $this->managerRegistry->getManager()->flush();
        $this->eventDispatcher->dispatch(new NodesSourcesUpdatedEvent($entity));

        $msg = $this->translator->trans('node_source.%node_source%.updated.%translation%', [
            '%node_source%' => $entity->getNode()->getNodeName(),
            '%translation%' => $entity->getTranslation()->getName(),
        ]);

        $this->logTrail->publishConfirmMessage($request, $msg, $entity);
    }

    #[\Override]
    protected function getPostUpdateRedirection(PersistableInterface $entity): Response
    {
        if (!$entity instanceof NodesSources) {
            throw new \InvalidArgumentException('Entity must be an instance of NodesSources');
        }

        /** @var Translation $translation */
        $translation = $entity->getTranslation();

        return $this->redirectToRoute(
            'nodesEditSourcePage',
            [
                'nodeId' => $entity->getNode()->getId(),
                'translationId' => $translation->getId(),
            ]
        );
    }
}
