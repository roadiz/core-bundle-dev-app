<?php

declare(strict_types=1);

namespace Themes\Rozier\Widgets;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Themes\Rozier\Utils\SessionListFilters;

/**
 * Prepare a Node tree according to Node hierarchy and given options.
 */
final class NodeTreeWidget extends AbstractWidget
{
    public const SESSION_ITEM_PER_PAGE = 'nodetree_item_per_page';
    protected ?Node $parentNode = null;
    /**
     * @var array<Node>|Paginator<Node>|null
     */
    protected $nodes = null;
    protected ?Tag $tag = null;
    protected ?TranslationInterface $translation = null;
    protected bool $stackTree = false;
    protected ?array $filters = null;
    protected bool $canReorder = true;
    protected array $additionalCriteria = [];

    /**
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     * @param Node|null $parent Entry point of NodeTreeWidget, set null if it's root
     * @param TranslationInterface|null $translation NodeTree translation
     */
    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        ?Node $parent = null,
        ?TranslationInterface $translation = null
    ) {
        parent::__construct($requestStack, $managerRegistry);

        $this->parentNode = $parent;
        $this->translation = $translation;
    }

    /**
     * @return Tag|null
     */
    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag|null $tag
     *
     * @return $this
     */
    public function setTag(?Tag $tag): NodeTreeWidget
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStackTree(): bool
    {
        return $this->stackTree;
    }

    /**
     * @param bool $newstackTree
     *
     * @return $this
     */
    public function setStackTree(bool $newstackTree): NodeTreeWidget
    {
        $this->stackTree = (bool) $newstackTree;

        return $this;
    }

    /**
     * Fill twig assignation array with NodeTree entities.
     */
    protected function getRootListManager(): EntityListManager
    {
        /*
         * Only use additional criteria for ROOT list-manager
         */
        return $this->getListManager($this->parentNode, false, $this->additionalCriteria);
    }

    /**
     * @return array
     */
    public function getAdditionalCriteria(): array
    {
        return $this->additionalCriteria;
    }

    /**
     * @param array $additionalCriteria
     *
     * @return NodeTreeWidget
     */
    public function setAdditionalCriteria(array $additionalCriteria): NodeTreeWidget
    {
        $this->additionalCriteria = $additionalCriteria;
        return $this;
    }

    /**
     * @param Node|null $parent
     * @param bool $subRequest
     *
     * @return bool
     */
    protected function canOrderByParent(Node $parent = null, bool $subRequest = false): bool
    {
        if (true === $subRequest || null === $parent) {
            return false;
        }

        if (
            $parent->getChildrenOrder() !== 'position' &&
            in_array($parent->getChildrenOrder(), Node::$orderingFields) &&
            in_array($parent->getChildrenOrderDirection(), ['ASC', 'DESC'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param Node|null $parent
     * @param bool $subRequest Default: false
     * @param array $additionalCriteria Default: []
     * @return EntityListManager
     */
    protected function getListManager(
        Node $parent = null,
        bool $subRequest = false,
        array $additionalCriteria = []
    ): EntityListManager {
        $criteria = array_merge($additionalCriteria, [
            'parent' => $parent,
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
        /*
         * Manage get request to filter list
         */
        $listManager = new EntityListManager(
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
            $sessionListFilter = new SessionListFilters(static::SESSION_ITEM_PER_PAGE);
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
     * @param Node|null $parent
     * @param bool $subRequest Default: false
     * @return array<int, Node>|Paginator<Node>
     */
    public function getChildrenNodes(Node $parent = null, bool $subRequest = false): iterable
    {
        return $this->getListManager($parent, $subRequest)->getEntities();
    }

    /**
     * @param Node|null $parent
     * @param bool $subRequest Default: false
     * @return array<int, Node>|Paginator<Node>
     */
    public function getReachableChildrenNodes(Node $parent = null, bool $subRequest = false): iterable
    {
        return $this->getListManager($parent, $subRequest, [
            'nodeType.reachable' => true,
        ])->getEntities();
    }

    /**
     * @return Node|null
     */
    public function getRootNode(): ?Node
    {
        return $this->parentNode;
    }

    /**
     * Get entity list manager filters.
     *
     * Call getNodes() first to populate this.
     *
     * @return array|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    /**
     * @return TranslationInterface
     */
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
     * @return array<int, Node>|Paginator<Node>
     */
    public function getNodes(): iterable
    {
        if (null === $this->nodes) {
            $manager = $this->getRootListManager();
            $this->nodes = $manager->getEntities();
            $this->filters = $manager->getAssignation();
        }

        return $this->nodes;
    }

    /**
     * Gets the value of canReorder.
     *
     * @return bool
     */
    public function getCanReorder(): bool
    {
        return $this->canReorder;
    }
}
