<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Widget;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\ListManager\NodeTreeDtoListManager;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Model\NodeTreeDto;
use RZ\Roadiz\CoreBundle\Model\TagTreeDto;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepare a Node tree according to Node hierarchy and given options.
 */
final class NodeTreeWidget extends AbstractWidget
{
    public const string SESSION_ITEM_PER_PAGE = 'nodetree_item_per_page';
    /**
     * @var array<NodeInterface>|null
     */
    private ?array $nodes = null;
    private ?Tag $tag = null;
    private bool $stackTree = false;
    private ?array $filters = null;
    private bool $canReorder = true;
    private array $additionalCriteria = [];

    /**
     * @param Node|null                 $parentNode  Entry point of NodeTreeWidget, set null if it's root
     * @param TranslationInterface|null $translation NodeTree translation
     */
    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly ?Node $parentNode = null,
        private readonly ?TranslationInterface $translation = null,
        private readonly bool $includeRootNode = false,
    ) {
        parent::__construct($requestStack, $managerRegistry);
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    /**
     * @return $this
     */
    public function setTag(?Tag $tag): NodeTreeWidget
    {
        $this->tag = $tag;

        return $this;
    }

    public function isStackTree(): bool
    {
        return $this->stackTree;
    }

    /**
     * @return $this
     */
    public function setStackTree(bool $stackTree): NodeTreeWidget
    {
        $this->stackTree = $stackTree;

        return $this;
    }

    /**
     * Fill twig assignation array with NodeTree entities.
     */
    protected function getRootListManager(): NodeTreeDtoListManager
    {
        /*
         * Only use additional criteria for ROOT list-manager
         */
        return $this->getListManager($this->parentNode, false, $this->additionalCriteria);
    }

    public function getAdditionalCriteria(): array
    {
        return $this->additionalCriteria;
    }

    public function setAdditionalCriteria(array $additionalCriteria): NodeTreeWidget
    {
        $this->additionalCriteria = $additionalCriteria;

        return $this;
    }

    protected function canOrderByParent(?NodeInterface $parent = null, bool $subRequest = false): bool
    {
        if (true === $subRequest || null === $parent) {
            return false;
        }

        if (
            'position' !== $parent->getChildrenOrder()
            && in_array($parent->getChildrenOrder(), Node::$orderingFields)
            && in_array($parent->getChildrenOrderDirection(), ['ASC', 'DESC'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param bool  $subRequest         Default: false
     * @param array $additionalCriteria Default: []
     */
    protected function getListManager(
        ?NodeInterface $parent = null,
        bool $subRequest = false,
        array $additionalCriteria = [],
    ): NodeTreeDtoListManager {
        $criteria = array_merge($additionalCriteria, [
            'parent' => $parent?->getId() ?? null,
            'translation' => $this->translation,
        ]);

        if (null !== $this->tag) {
            $criteria['tags'] = $this->tag;
        }

        $ordering = [
            'position' => 'ASC',
        ];

        if (null !== $parent && $this->canOrderByParent($parent, $subRequest)) {
            $ordering = [
                $parent->getChildrenOrder() => $parent->getChildrenOrderDirection(),
            ];
            $this->canReorder = false;
        }

        $listManager = new NodeTreeDtoListManager(
            $this->getRequest(),
            $this->getManagerRegistry()->getManager(),
            Node::class,
            $criteria,
            $ordering
        );
        $listManager->setDisplayingNotPublishedNodes(true);

        if (true === $this->stackTree) {
            $listManager->setItemPerPage(20);
            $listManager->handle();

            /*
             * Stored in session
             */
            $sessionListFilter = new SessionListFilters(self::SESSION_ITEM_PER_PAGE);
            $sessionListFilter->handleItemPerPage($this->getRequest(), $listManager);
        } else {
            $listManager->setItemPerPage(99999);
            $listManager->handle(true);
        }

        if ($subRequest) {
            $listManager->disablePagination();
        }

        return $listManager;
    }

    /**
     * @param bool $subRequest Default: false
     *
     * @return array<NodeTreeDto>
     *
     * @throws \ReflectionException
     */
    public function getChildrenNodes(?NodeInterface $parent = null, bool $subRequest = false): array
    {
        return $this->getListManager($parent, $subRequest)->getEntities();
    }

    /**
     * @param bool $subRequest Default: false
     *
     * @return array<NodeTreeDto>
     *
     * @throws \ReflectionException
     */
    public function getReachableChildrenNodes(?NodeInterface $parent = null, bool $subRequest = false): array
    {
        return $this->getListManager($parent, $subRequest, [
            'nodeTypeName' => array_map(fn (NodeType $nodeType) => $nodeType->getName(), $this->nodeTypesBag->allReachable()),
        ])->getEntities();
    }

    public function getRootNode(): ?Node
    {
        return $this->parentNode;
    }

    /**
     * Get entity list manager filters.
     *
     * Call getNodes() first to populate this.
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    #[\Override]
    public function getTranslation(): TranslationInterface
    {
        return $this->translation ?? parent::getTranslation();
    }

    /**
     * @return array<TranslationInterface>
     */
    public function getAvailableTranslations(): array
    {
        return $this->getManagerRegistry()
            ->getRepository(TranslationInterface::class)
            ->findBy([], [
                'defaultTranslation' => 'DESC',
                'locale' => 'ASC',
            ]);
    }

    /**
     * @return array<NodeInterface>
     */
    public function getNodes(): array
    {
        if ($this->includeRootNode && null !== $this->getRootNode()) {
            return [$this->getRootNode()];
        }
        if (null === $this->nodes) {
            $manager = $this->getRootListManager();
            $this->nodes = $manager->getEntities();
            $this->filters = $manager->getAssignation();
        }

        return $this->nodes;
    }

    /**
     * @return array<TagTreeDto>
     */
    public function getTags(?NodeInterface $node): array
    {
        if (null === $node) {
            return [];
        }

        return $this->managerRegistry->getRepository(Tag::class)->findByAsTagTreeDto([
            'nodes' => $node->getId(),
        ], [
            'position' => 'ASC',
        ], null, null, $this->getTranslation());
    }

    public function getOneDisplayableDocument(NodeTreeDto $node): ?DocumentDto
    {
        return $this->managerRegistry
            ->getRepository(Document::class)
            ->findOneDisplayableDtoByNodeSource(
                $node->getNodeSource()->getId(),
            );
    }

    /**
     * Gets the value of canReorder.
     */
    public function getCanReorder(): bool
    {
        return $this->canReorder;
    }
}
