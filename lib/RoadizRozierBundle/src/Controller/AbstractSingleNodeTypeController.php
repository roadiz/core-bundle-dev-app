<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\CoreBundle\Workflow\NodeWorkflow;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base AbstractAdminWithBulkController for listing NodesSources of the same node-type.
 *
 * @template TEntity of NodesSources
 * @template TInputDto of object
 */
abstract class AbstractSingleNodeTypeController extends AbstractAdminWithBulkController
{
    protected ?Tag $tag = null;
    protected ?Node $shadowContainer = null;

    public function __construct(
        protected readonly UniqueNodeGenerator $uniqueNodeGenerator,
        protected readonly NodeTypes $nodeTypes,
        protected readonly TranslationRepository $translationRepository,
        protected readonly AllStatusesNodeRepository $nodeRepository,
        protected readonly NodeNamePolicyInterface $nodeNamePolicy,
        protected readonly NodeWorkflow $workflow,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($formFactory, $urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    #[\Override]
    protected function getRepository(): ObjectRepository
    {
        /** @var NodesSourcesRepository<TEntity> $repository */
        $repository = $this->managerRegistry->getRepository($this->getEntityClass());
        $repository->setDisplayingNotPublishedNodes(true);

        return $repository;
    }

    #[\Override]
    protected function additionalAssignation(Request $request): void
    {
        parent::additionalAssignation($request);

        $this->tag = null;

        if (!empty($request->query->get('tag'))) {
            $this->tag = $this->managerRegistry
                ->getRepository(Tag::class)
                ->find((int) $request->query->get('tag'));
            $this->assignation['currentTag'] = $this->tag;
        }

        $this->assignation['tags'] = $this->getUsedTags();
        $this->assignation['shadowTitle'] = false !== $this->getShadowContainer()->getNodeSources()->first()
            ? $this->getShadowContainer()->getNodeSources()->first()->getTitle()
            : $this->getShadowContainer()->getNodeName();
    }

    /**
     * @return array<string, array<Tag>>
     */
    protected function getUsedTags(): array
    {
        /** @var QueryBuilder $tagQb */
        $tagQb = $this->managerRegistry->getRepository(Tag::class)->createQueryBuilder('t');

        /** @var Tag[] $tags */
        $tags = $tagQb
            ->innerJoin('t.nodesTags', 'nt')
            ->innerJoin('nt.node', 'n')
            ->andWhere($tagQb->expr()->eq('n.nodeTypeName', ':nodeTypeName'))
            ->andWhere($tagQb->expr()->lte('n.status', ':status'))
            ->addOrderBy('t.position', 'ASC')
            ->setParameter('nodeTypeName', $this->getNodeTypeName())
            ->setParameter('status', NodeStatus::PUBLISHED)
            ->getQuery()
            ->getResult();

        $groupedTags = [];
        foreach ($tags as $tag) {
            $group = $tag->getParent()?->getName() ?? 'Other';
            if (!array_key_exists($group, $groupedTags)) {
                $groupedTags[$group] = [];
            }
            $groupedTags[$group][] = $tag;
        }

        return $groupedTags;
    }

    /**
     * @return TEntity A new empty node source, not flushed
     */
    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        // @phpstan-ignore-next-line
        return $this->uniqueNodeGenerator->generate(
            $this->nodeTypes->get($this->getNodeTypeName()) ?? throw new \RuntimeException(sprintf('Node type %s is not defined.', $this->getNodeTypeName())),
            $this->translationRepository->findDefault() ?? throw new \RuntimeException('No default translation found.'),
            // @phpstan-ignore-next-line
            $this->nodeRepository->findOneByNodeName($this->getShadowRootNodeName()) ?? throw new \RuntimeException(sprintf('No shadow root node "%s" found.', $this->getShadowRootNodeName())),
            pushToTop: false, // Do not push to top, it would update all presentations updateAt date
            flush: false,
        );
    }

    /**
     * @param TEntity $template
     *
     * @return TEntity A new duplicated node source, flushed
     */
    protected function createFromDuplication(NodesSources $template): NodesSources
    {
        // Duplicate from selected presentation template
        $duplicator = new NodeDuplicator(
            $template->getNode(),
            $this->managerRegistry->getManagerForClass(Node::class) ?? throw new \RuntimeException('No entity manager found for Node class.'),
            $this->nodeNamePolicy
        );
        $duplicatedNode = $duplicator->duplicate();

        // @phpstan-ignore-next-line
        return $duplicatedNode->getNodeSources()->first() ?: throw new \RuntimeException('Duplicated node has no source.');
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return mb_strtolower($this->getNodeTypeName());
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        throw new \LogicException('Use default node CRUD routes');
    }

    protected function getShadowContainer(): Node
    {
        if (null === $this->shadowContainer) {
            $this->shadowContainer = $this->nodeRepository->findOneByNodeName($this->getShadowRootNodeName()) ?? throw new \RuntimeException(sprintf('No shadow root node "%s" found.', $this->getShadowRootNodeName()));
        }

        return $this->shadowContainer;
    }

    #[\Override]
    protected function getDefaultCriteria(Request $request): array
    {
        $criteria = [
            'node.parent' => $this->getShadowContainer(),
            'translation' => $this->translationRepository->findDefault() ?? throw new \RuntimeException('No default translation found.'),
        ];
        if (null !== $this->tag) {
            $criteria['tags'] = [$this->tag];
        }

        return $criteria;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        $shadowContainer = $this->getShadowContainer();
        $childOrderField = match ($shadowContainer->getChildrenOrder()) {
            'position' => 'node.position',
            'nodeName' => 'node.nodeName',
            'updatedAt' => 'node.updatedAt',
            'ns.publishedAt' => 'publishedAt',
            default => 'node.createdAt',
        };

        return [
            $childOrderField => $shadowContainer->getChildrenOrderDirection(),
        ];
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return 'admin/'.$this->getNamespace();
    }

    #[\Override]
    protected function setPublishedAt(PersistableInterface $item, ?\DateTimeInterface $dateTime): void
    {
        parent::setPublishedAt($item, $dateTime);

        if (!$item instanceof NodesSources) {
            return;
        }

        if (null !== $dateTime) {
            $dateTime = \DateTime::createFromInterface($dateTime);
        }

        $item->setPublishedAt($dateTime);

        if (null === $dateTime) {
            $this->workflow->apply($item->getNode(), 'reject');
        }

        if (null !== $dateTime && $dateTime <= new \DateTime()) {
            $this->workflow->apply($item->getNode(), 'publish');
        }
    }

    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return is_a($item, $this->getEntityClass());
    }

    /**
     * @param TEntity $item
     */
    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        return $item->getTitle() ?? $item->getNode()->getNodeName();
    }

    /**
     * @param TEntity $item
     *
     * @return \Symfony\Contracts\EventDispatcher\Event[]
     */
    #[\Override]
    protected function createCreateEvent(PersistableInterface $item): array
    {
        return [
            new NodeCreatedEvent($item->getNode()),
            new NodesSourcesCreatedEvent($item),
        ];
    }

    /**
     * @param TEntity $item
     */
    protected function redirectToEditPage(PersistableInterface $item): RedirectResponse
    {
        return $this->redirectToRoute(
            'nodesEditSourcePage',
            ['nodeId' => $item->getNode()->getId(), 'translationId' => $item->getTranslation()->getId()],
        );
    }

    /**
     * @param TInputDto $input Input DTO returned by createInputDto method
     *
     * @return TEntity Returns a populated item from input DTO
     */
    protected function populateItem(object $input, Request $request): NodesSources
    {
        return $this->createEmptyItem($request);
    }

    #[\Override]
    public function addAction(Request $request): ?Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredCreationRole());
        $this->additionalAssignation($request);

        $input = $this->createInputDto();
        $form = $this->createForm($this->getFormType(), $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Creates and populates item from input DTO and request.
             */
            $item = $this->populateItem($input, $request);

            $entityManager = $this->managerRegistry
                ->getManagerForClass($this->getEntityClass()) ?? throw new \RuntimeException('No entity manager found for '.$this->getEntityClass().' class.');
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createCreateEvent($item);
            $this->dispatchSingleOrMultipleEvent($event);

            $entityManager->flush();

            $msg = $this->translator->trans(
                '%namespace%.%item%.was_created',
                [
                    '%item%' => $this->getEntityName($item),
                    '%namespace%' => $this->translator->trans($this->getNamespace()),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $item);

            return $this->redirectToEditPage($item);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $input;

        return $this->render(
            $this->getTemplateFolder().'/add.html.twig',
            $this->assignation,
        );
    }

    #[\Override]
    public function editAction(Request $request, int|string $id): RedirectResponse
    {
        /** @var TEntity|null $item */
        $item = $this->managerRegistry
            ->getRepository($this->getEntityClass())
            ->find($id);

        if (null === $item) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToEditPage($item);
    }

    #[\Override]
    public function deleteAction(Request $request, int|string $id): ?Response
    {
        throw new \LogicException('Use default node CRUD routes');
    }

    #[\Override]
    protected function getBulkPublishRouteName(): ?string
    {
        return 'appArticlesBulkPublishPage';
    }

    #[\Override]
    protected function getBulkUnpublishRouteName(): ?string
    {
        return 'appArticlesBulkUnpublishPage';
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): ?string
    {
        return 'appArticlesBulkDeletePage';
    }

    abstract protected function getShadowRootNodeName(): string;

    abstract protected function getNodeTypeName(): string;

    /**
     * @return class-string<TEntity>
     */
    #[\Override]
    abstract protected function getEntityClass(): string;

    /**
     * @return TInputDto
     */
    abstract protected function createInputDto(): object;
}
