<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
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
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Workflow\Registry;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Widgets\TreeWidgetFactory;
use Twig\Error\RuntimeError;

class NodesTreesController extends RozierApp
{
    public function __construct(
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly Registry $workflowRegistry,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function treeAction(Request $request, ?int $nodeId = null, ?int $translationId = null): Response
    {
        if (null !== $nodeId) {
            /** @var Node|null $node */
            $node = $this->em()->find(Node::class, $nodeId);
            if (null === $node) {
                throw new ResourceNotFoundException();
            }
            $this->em()->refresh($node);
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
            $translation = $this->em()
                                ->getRepository(Translation::class)
                                ->findOneBy(['id' => $translationId]);
        } else {
            /** @var Translation $translation */
            $translation = $this->em()->getRepository(Translation::class)->findDefault();
        }

        $widget = $this->treeWidgetFactory->createNodeTree($node, $translation);

        if (
            $request->get('tagId')
            && $request->get('tagId') > 0
        ) {
            $filterTag = $this->em()->find(Tag::class, (int) $request->get('tagId'));
            $this->assignation['filterTag'] = $filterTag;
            $widget->setTag($filterTag);
        }

        $widget->setStackTree(true);
        $widget->getNodes(); // pre-fetch nodes for enable filters

        if (null !== $node) {
            $this->assignation['node'] = $node;

            if ($node->isHidingChildren()) {
                $this->assignation['availableTags'] = $this->em()->getRepository(Tag::class)->findAllLinkedToNodeChildren(
                    $node,
                    $translation
                );
            }
            $this->assignation['source'] = $node->getNodeSourcesByTranslation($translation)->first();
            $availableTranslations = $this->em()
                ->getRepository(Translation::class)
                ->findAvailableTranslationsForNode($node);
            $this->assignation['available_translations'] = $availableTranslations;
        }
        $this->assignation['translation'] = $translation;
        $this->assignation['specificNodeTree'] = $widget;

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
                $msg = $this->getTranslator()->trans('wrong.request');
            }

            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute(
                'nodesTreePage',
                ['nodeId' => $nodeId, 'translationId' => $translationId]
            );
        }
        $this->assignation['tagNodesForm'] = $tagNodesForm->createView();

        /*
         * Handle bulk status
         */
        if ($this->isGranted('ROLE_ACCESS_NODES_STATUS')) {
            $statusBulkNodes = $this->buildBulkStatusForm($request->getRequestUri());
            $this->assignation['statusNodesForm'] = $statusBulkNodes->createView();
        }

        if ($this->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            /*
             * Handle bulk delete form
             */
            $deleteNodesForm = $this->buildBulkDeleteForm($request->getRequestUri());
            $this->assignation['deleteNodesForm'] = $deleteNodesForm->createView();
        }

        return $this->render('@RoadizRozier/nodes/tree.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function bulkDeleteAction(Request $request): Response
    {
        if (empty($request->get('deleteForm')['nodesIds'])) {
            throw new ResourceNotFoundException();
        }

        $nodesIds = trim($request->get('deleteForm')['nodesIds']);
        $nodesIds = explode(',', $nodesIds);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->em()
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
            $this->publishConfirmMessage($request, $msg);

            if (!empty($form->getData()['referer'])) {
                return $this->redirect($form->getData()['referer']);
            } else {
                return $this->redirectToRoute('nodesHomePage');
            }
        }

        $this->assignation['nodes'] = $nodes;
        $this->assignation['form'] = $form->createView();

        if (!empty($request->get('deleteForm')['referer'])) {
            $this->assignation['referer'] = $request->get('deleteForm')['referer'];
        }

        return $this->render('@RoadizRozier/nodes/bulkDelete.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function bulkStatusAction(Request $request): Response
    {
        if (empty($request->get('statusForm')['nodesIds'])) {
            throw new ResourceNotFoundException();
        }

        $nodesIds = trim($request->get('statusForm')['nodesIds']);
        $nodesIds = explode(',', $nodesIds);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->em()
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
            $this->publishConfirmMessage($request, $msg);

            if (!empty($form->getData()['referer'])) {
                return $this->redirect($form->getData()['referer']);
            } else {
                return $this->redirectToRoute('nodesHomePage');
            }
        }

        $this->assignation['nodes'] = $nodes;
        $this->assignation['form'] = $form->createView();

        if (!empty($request->get('statusForm')['referer'])) {
            $this->assignation['referer'] = $request->get('statusForm')['referer'];
        }

        return $this->render('@RoadizRozier/nodes/bulkStatus.html.twig', $this->assignation);
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

    /**
     * @return string
     */
    private function bulkDeleteNodes(array $data)
    {
        if (!empty($data['nodesIds'])) {
            $nodesIds = trim($data['nodesIds']);
            $nodesIds = explode(',', $nodesIds);
            array_filter($nodesIds);

            $nodes = $this->em()
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

            $this->em()->flush();

            return $this->getTranslator()->trans('nodes.bulk.deleted');
        }

        return $this->getTranslator()->trans('wrong.request');
    }

    private function bulkStatusNodes(array $data): string
    {
        if (!empty($data['nodesIds'])) {
            $nodesIds = trim($data['nodesIds']);
            $nodesIds = explode(',', $nodesIds);
            array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->em()
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
            $this->em()->flush();

            return $this->getTranslator()->trans('nodes.bulk.status.changed');
        }

        return $this->getTranslator()->trans('wrong.request');
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

    /**
     * @return string
     */
    private function tagNodes(array $data)
    {
        $msg = $this->getTranslator()->trans('nodes.bulk.not_tagged');

        if (
            !empty($data['tagsPaths'])
            && !empty($data['nodesIds'])
        ) {
            $nodesIds = explode(',', $data['nodesIds']);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->em()
                          ->getRepository(Node::class)
                          ->setDisplayingNotPublishedNodes(true)
                          ->findBy([
                              'id' => $nodesIds,
                          ]);

            $paths = explode(',', $data['tagsPaths']);
            $paths = array_filter($paths);

            foreach ($paths as $path) {
                $tag = $this->em()
                            ->getRepository(Tag::class)
                            ->findOrCreateByPath($path);

                foreach ($nodes as $node) {
                    $node->addTag($tag);
                }
            }
            $msg = $this->getTranslator()->trans('nodes.bulk.tagged');
        }

        $this->em()->flush();

        return $msg;
    }

    /**
     * @return string
     */
    private function untagNodes(array $data)
    {
        $msg = $this->getTranslator()->trans('nodes.bulk.not_untagged');

        if (
            !empty($data['tagsPaths'])
            && !empty($data['nodesIds'])
        ) {
            $nodesIds = explode(',', $data['nodesIds']);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->em()
                          ->getRepository(Node::class)
                          ->setDisplayingNotPublishedNodes(true)
                          ->findBy([
                              'id' => $nodesIds,
                          ]);

            $paths = explode(',', $data['tagsPaths']);
            $paths = array_filter($paths);

            foreach ($paths as $path) {
                $tag = $this->em()
                            ->getRepository(Tag::class)
                            ->findByPath($path);

                if (null !== $tag) {
                    foreach ($nodes as $node) {
                        $node->removeTag($tag);
                    }
                }
            }
            $msg = $this->getTranslator()->trans('nodes.bulk.untagged');
        }

        $this->em()->flush();

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
                    Node::getStatusLabel(Node::DRAFT) => 'reject',
                    Node::getStatusLabel(Node::PENDING) => 'review',
                    Node::getStatusLabel(Node::PUBLISHED) => 'publish',
                    Node::getStatusLabel(Node::ARCHIVED) => 'archive',
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
