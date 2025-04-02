<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\Widgets\TreeWidgetFactory;
use Twig\Error\RuntimeError;

#[AsController]
final class NodesTreesController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly Registry $workflowRegistry,
        private readonly LogTrail $logTrail,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function treeAction(Request $request, ?int $nodeId = null, ?int $translationId = null): Response
    {
        $assignation = [];

        if (null !== $nodeId) {
            /** @var Node|null $node */
            $node = $this->managerRegistry->getRepository(Node::class)->find($nodeId);
            if (null === $node) {
                throw new ResourceNotFoundException();
            }
            $this->managerRegistry->getManager()->refresh($node);
        } elseif (null !== $user = $this->getUser()) {
            $node = $this->nodeChrootResolver->getChroot($user);
        } else {
            $node = null;
        }

        if (null !== $node) {
            $this->denyAccessUnlessGranted(NodeVoter::READ, $node);
        } else {
            $this->denyAccessUnlessGranted(NodeVoter::READ_AT_ROOT);
        }

        if (null !== $translationId) {
            /** @var Translation $translation */
            $translation = $this->managerRegistry
                                ->getRepository(Translation::class)
                                ->findOneBy(['id' => $translationId]);
        } else {
            /** @var Translation $translation */
            $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        }

        $widget = $this->treeWidgetFactory->createNodeTree($node, $translation);

        if (
            $request->get('tagId')
            && $request->get('tagId') > 0
        ) {
            $filterTag = $this->managerRegistry
                ->getRepository(Tag::class)
                ->find((int) $request->get('tagId'));
            $assignation['filterTag'] = $filterTag;
            $widget->setTag($filterTag);
        }

        $widget->setStackTree(true);
        $widget->getNodes(); // pre-fetch nodes for enable filters

        if (null !== $node) {
            $assignation['node'] = $node;

            if ($node->isHidingChildren()) {
                $assignation['availableTags'] = $this->managerRegistry
                    ->getRepository(Tag::class)
                    ->findAllLinkedToNodeChildren(
                        $node,
                        $translation
                    );
            }
            $assignation['source'] = $node->getNodeSourcesByTranslation($translation)->first();
            $availableTranslations = $this->managerRegistry
                ->getRepository(Translation::class)
                ->findAvailableTranslationsForNode($node);
            $assignation['available_translations'] = $availableTranslations;
        }
        $assignation['translation'] = $translation;
        $assignation['specificNodeTree'] = $widget;

        /*
         * Handle bulk tag form
         */
        $tagNodesForm = $this->buildBulkTagForm();
        $tagNodesForm->handleRequest($request);
        if ($tagNodesForm->isSubmitted() && $tagNodesForm->isValid()) {
            $data = $tagNodesForm->getData();

            $submitTag = $tagNodesForm->get('submitTag');
            $submitUntag = $tagNodesForm->get('submitUntag');
            if ($submitTag instanceof ClickableInterface && $submitTag->isClicked()) {
                $msg = $this->tagNodes($data);
            } elseif ($submitUntag instanceof ClickableInterface && $submitUntag->isClicked()) {
                $msg = $this->untagNodes($data);
            } else {
                $msg = $this->translator->trans('wrong.request');
            }

            $this->logTrail->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute(
                'nodesTreePage',
                ['nodeId' => $nodeId, 'translationId' => $translationId]
            );
        }
        $assignation['tagNodesForm'] = $tagNodesForm->createView();

        /*
         * Handle bulk status
         */
        if ($this->isGranted('ROLE_ACCESS_NODES_STATUS')) {
            $statusBulkNodes = $this->buildBulkStatusForm($request->getRequestUri());
            $assignation['statusNodesForm'] = $statusBulkNodes->createView();
        }

        if ($this->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            /*
             * Handle bulk delete form
             */
            $deleteNodesForm = $this->buildBulkDeleteForm($request->getRequestUri());
            $assignation['deleteNodesForm'] = $deleteNodesForm->createView();
        }

        return $this->render('@RoadizRozier/nodes/tree.html.twig', $assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function bulkDeleteAction(Request $request): Response
    {
        if (empty($request->get('deleteForm')['nodesIds'])) {
            throw new ResourceNotFoundException();
        }

        $assignation = [];

        $nodesIds = trim($request->get('deleteForm')['nodesIds']);
        $nodesIds = explode(',', $nodesIds);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->managerRegistry
                      ->getRepository(Node::class)
                      ->setDisplayingNotPublishedNodes(true)
                      ->findBy([
                          'id' => $nodesIds,
                      ]);

        if (0 === count($nodes)) {
            throw new ResourceNotFoundException();
        }

        foreach ($nodes as $node) {
            $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);
        }

        $form = $this->buildBulkDeleteForm(
            $request->get('deleteForm')['referer'],
            $nodesIds
        );
        $form->handleRequest($request);
        if ($request->get('confirm') && $form->isSubmitted() && $form->isValid()) {
            $msg = $this->bulkDeleteNodes($form->getData());
            $this->logTrail->publishConfirmMessage($request, $msg);

            if (!empty($form->getData()['referer'])) {
                return $this->redirect($form->getData()['referer']);
            } else {
                return $this->redirectToRoute('nodesHomePage');
            }
        }

        $assignation['nodes'] = $nodes;
        $assignation['form'] = $form->createView();

        if (!empty($request->get('deleteForm')['referer'])) {
            $assignation['referer'] = $request->get('deleteForm')['referer'];
        }

        return $this->render('@RoadizRozier/nodes/bulkDelete.html.twig', $assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function bulkStatusAction(Request $request): Response
    {
        if (empty($request->get('statusForm')['nodesIds'])) {
            throw new ResourceNotFoundException();
        }

        $assignation = [];

        $nodesIds = trim($request->get('statusForm')['nodesIds']);
        $nodesIds = explode(',', $nodesIds);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->managerRegistry
                      ->getRepository(Node::class)
                      ->setDisplayingNotPublishedNodes(true)
                      ->findBy([
                          'id' => $nodesIds,
                      ]);

        if (0 === count($nodes)) {
            throw new ResourceNotFoundException();
        }

        foreach ($nodes as $node) {
            $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);
        }

        $form = $this->buildBulkStatusForm(
            $request->get('statusForm')['referer'],
            $nodesIds,
            (string) $request->get('statusForm')['status']
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->bulkStatusNodes($form->getData());
            $this->logTrail->publishConfirmMessage($request, $msg);

            if (!empty($form->getData()['referer'])) {
                return $this->redirect($form->getData()['referer']);
            } else {
                return $this->redirectToRoute('nodesHomePage');
            }
        }

        $assignation['nodes'] = $nodes;
        $assignation['form'] = $form->createView();

        if (!empty($request->get('statusForm')['referer'])) {
            $assignation['referer'] = $request->get('statusForm')['referer'];
        }

        return $this->render('@RoadizRozier/nodes/bulkStatus.html.twig', $assignation);
    }

    private function buildBulkDeleteForm(
        ?string $referer = null,
        array $nodesIds = [],
    ): FormInterface {
        /** @var FormBuilder $builder */
        $builder = $this->formFactory
                        ->createNamedBuilder('deleteForm')
                        ->add('nodesIds', HiddenType::class, [
                            'data' => implode(',', $nodesIds),
                            'attr' => ['class' => 'nodes-id-bulk-tags'],
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        if (null !== $referer && (new UnicodeString($referer))->startsWith('/')) {
            $builder->add('referer', HiddenType::class, [
                'data' => $referer,
            ]);
        }

        return $builder->getForm();
    }

    private function bulkDeleteNodes(array $data): string
    {
        if (!empty($data['nodesIds'])) {
            $nodesIds = trim($data['nodesIds']);
            $nodesIds = explode(',', $nodesIds);
            array_filter($nodesIds);

            $nodes = $this->managerRegistry
                          ->getRepository(Node::class)
                          ->setDisplayingNotPublishedNodes(true)
                          ->findBy([
                              'id' => $nodesIds,
                          ]);

            /** @var Node $node */
            foreach ($nodes as $node) {
                /** @var NodeHandler $handler */
                $handler = $this->handlerFactory->getHandler($node);
                $handler->softRemoveWithChildren();
            }

            $this->managerRegistry->getManager()->flush();

            return $this->translator->trans('nodes.bulk.deleted');
        }

        return $this->translator->trans('wrong.request');
    }

    private function bulkStatusNodes(array $data): string
    {
        if (!empty($data['nodesIds'])) {
            $nodesIds = trim($data['nodesIds']);
            $nodesIds = explode(',', $nodesIds);
            array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->managerRegistry
                ->getRepository(Node::class)
                ->setDisplayingNotPublishedNodes(true)
                ->findBy([
                    'id' => $nodesIds,
                ])
            ;

            foreach ($nodes as $node) {
                $workflow = $this->workflowRegistry->get($node);
                if ($workflow->can($node, $data['status'])) {
                    $workflow->apply($node, $data['status']);
                }
            }
            $this->managerRegistry->getManager()->flush();

            return $this->translator->trans('nodes.bulk.status.changed');
        }

        return $this->translator->trans('wrong.request');
    }

    private function buildBulkTagForm(): FormInterface
    {
        /** @var FormBuilder $builder */
        $builder = $this->formFactory
            ->createNamedBuilder('tagForm')
            ->add('nodesIds', HiddenType::class, [
                'attr' => ['class' => 'nodes-id-bulk-tags'],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('tagsPaths', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'rz-tag-autocomplete',
                    'placeholder' => 'list.tags.to_link.or_unlink',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('submitTag', SubmitType::class, [
                'label' => 'link.tags',
                'attr' => [
                    'class' => 'uk-button uk-button-primary',
                    'title' => 'link.tags',
                    'data-uk-tooltip' => '{animation:true}',
                ],
            ])
            ->add('submitUntag', SubmitType::class, [
                'label' => 'unlink.tags',
                'attr' => [
                    'class' => 'uk-button',
                    'title' => 'unlink.tags',
                    'data-uk-tooltip' => '{animation:true}',
                ],
            ])
        ;

        return $builder->getForm();
    }

    private function tagNodes(array $data): string
    {
        $msg = $this->translator->trans('nodes.bulk.not_tagged');

        if (
            !empty($data['tagsPaths'])
            && !empty($data['nodesIds'])
        ) {
            $nodesIds = explode(',', $data['nodesIds']);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->managerRegistry
                          ->getRepository(Node::class)
                          ->setDisplayingNotPublishedNodes(true)
                          ->findBy([
                              'id' => $nodesIds,
                          ]);

            $paths = explode(',', $data['tagsPaths']);
            $paths = array_filter($paths);

            foreach ($paths as $path) {
                $tag = $this->managerRegistry
                            ->getRepository(Tag::class)
                            ->findOrCreateByPath($path);

                foreach ($nodes as $node) {
                    $node->addTag($tag);
                }
            }
            $msg = $this->translator->trans('nodes.bulk.tagged');
        }

        $this->managerRegistry->getManager()->flush();

        return $msg;
    }

    private function untagNodes(array $data): string
    {
        $msg = $this->translator->trans('nodes.bulk.not_untagged');

        if (
            !empty($data['tagsPaths'])
            && !empty($data['nodesIds'])
        ) {
            $nodesIds = explode(',', $data['nodesIds']);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->managerRegistry
                          ->getRepository(Node::class)
                          ->setDisplayingNotPublishedNodes(true)
                          ->findBy([
                              'id' => $nodesIds,
                          ]);

            $paths = explode(',', $data['tagsPaths']);
            $paths = array_filter($paths);

            foreach ($paths as $path) {
                $tag = $this->managerRegistry
                            ->getRepository(Tag::class)
                            ->findByPath($path);

                if (null !== $tag) {
                    foreach ($nodes as $node) {
                        $node->removeTag($tag);
                    }
                }
            }
            $msg = $this->translator->trans('nodes.bulk.untagged');
        }

        $this->managerRegistry->getManager()->flush();

        return $msg;
    }

    private function buildBulkStatusForm(
        ?string $referer = null,
        array $nodesIds = [],
        string $status = 'reject',
    ): FormInterface {
        /** @var FormBuilder $builder */
        $builder = $this->formFactory
            ->createNamedBuilder('statusForm')
            ->add('nodesIds', HiddenType::class, [
                'attr' => ['class' => 'nodes-id-bulk-status'],
                'data' => implode(',', $nodesIds),
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => false,
                'data' => $status,
                'choices' => [
                    NodeStatus::DRAFT->getLabel() => 'reject',
                    NodeStatus::PENDING->getLabel() => 'review',
                    NodeStatus::PUBLISHED->getLabel() => 'publish',
                    NodeStatus::ARCHIVED->getLabel() => 'archive',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
        ;

        if (null !== $referer && (new UnicodeString($referer))->startsWith('/')) {
            $builder->add('referer', HiddenType::class, [
                'data' => $referer,
            ]);
        }

        return $builder->getForm();
    }
}
