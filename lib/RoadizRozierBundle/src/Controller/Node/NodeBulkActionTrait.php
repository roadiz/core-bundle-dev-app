<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Twig\Error\RuntimeError;

trait NodeBulkActionTrait
{
    private function handleTagNodesForm(Request $request, FormInterface $tagNodesForm): ?Response
    {
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
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params'),
            );
        }

        return null;
    }

    /**
     * @throws RuntimeError
     */
    public function bulkDeleteAction(Request $request): Response
    {
        if (empty($request->get('deleteForm')['nodesIds'])) {
            throw new ResourceNotFoundException();
        }

        $nodesIds = trim((string) $request->get('deleteForm')['nodesIds']);
        $nodesIds = \json_decode($nodesIds, true, flags: JSON_THROW_ON_ERROR);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->allStatusesNodeRepository->findBy([
            'id' => $nodesIds,
        ]);

        if (0 === count($nodes)) {
            throw new ResourceNotFoundException();
        }

        $items = [];
        foreach ($nodes as $node) {
            $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);
            $items[] = $this->explorerItemFactory->createForEntity($node)->toArray();
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
            }

            return $this->redirectToRoute('nodesHomePage');
        }

        $referer = null;
        if (!empty($request->get('deleteForm')['referer'])) {
            $referer = $request->get('deleteForm')['referer'];
        }

        $title = new UnicodeString($this->translator->trans('delete.nodes'));
        $cancelPath = $referer ?? $this->generateUrl('nodesHomePage');

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/nodes/head.html.twig',
            'cancelPath' => $cancelPath,
            'alertMessage' => 'are_you_sure.delete.these.nodes',
            'form' => $form->createView(),
            'items' => $items,
        ]);
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

        $nodesIds = trim((string) $request->get('statusForm')['nodesIds']);
        $nodesIds = \json_decode($nodesIds, true, flags: JSON_THROW_ON_ERROR);
        array_filter($nodesIds);

        /** @var Node[] $nodes */
        $nodes = $this->allStatusesNodeRepository
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
            }

            return $this->redirectToRoute('nodesHomePage');
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
                'data' => json_encode($nodesIds, flags: JSON_THROW_ON_ERROR),
                'attr' => ['class' => 'bulk-form-value'],
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

        $builder->setAction('?confirm=1');

        return $builder->getForm();
    }

    private function bulkDeleteNodes(array $data): string
    {
        if (!empty($data['nodesIds'])) {
            $nodesIds = trim((string) $data['nodesIds']);
            $nodesIds = \json_decode($nodesIds, true, flags: JSON_THROW_ON_ERROR);
            array_filter($nodesIds);

            $nodes = $this->allStatusesNodeRepository
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
            $nodesIds = \json_decode((string) $data['nodesIds'], true, flags: JSON_THROW_ON_ERROR);
            array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->allStatusesNodeRepository
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
                'attr' => ['class' => 'bulk-form-value'],
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
                'label' => null,
                'attr' => [
                    'is' => 'rz-button',
                    'class' => 'rz-button rz-button--secondary',
                    'title' => $this->translator->trans('link.tags'),
                    'icon' => 'rz-icon-ri--add-line',
                ],
            ])
            ->add('submitUntag', SubmitType::class, [
                'label' => null,
                'attr' => [
                    'is' => 'rz-button',
                    'class' => 'rz-button rz-button--secondary',
                    'title' => $this->translator->trans('unlink.tags'),
                    'icon' => 'rz-icon-ri--subtract-line',
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
            $nodesIds = json_decode((string) $data['nodesIds'], true, flags: JSON_THROW_ON_ERROR);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->allStatusesNodeRepository
                ->findBy([
                    'id' => $nodesIds,
                ]);

            $paths = explode(',', (string) $data['tagsPaths']);
            $paths = array_filter($paths);

            foreach ($paths as $path) {
                $tag = $this->managerRegistry
                    ->getRepository(Tag::class)
                    ->findOrCreateByPath($path);
                if (null === $tag) {
                    continue;
                }
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
            $nodesIds = \json_decode((string) $data['nodesIds'], true, flags: JSON_THROW_ON_ERROR);
            $nodesIds = array_filter($nodesIds);

            /** @var Node[] $nodes */
            $nodes = $this->allStatusesNodeRepository
                ->findBy([
                    'id' => $nodesIds,
                ]);

            $paths = explode(',', (string) $data['tagsPaths']);
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
                'attr' => ['class' => 'bulk-form-value'],
                'data' => json_encode($nodesIds, flags: JSON_THROW_ON_ERROR),
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
