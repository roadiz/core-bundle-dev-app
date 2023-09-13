<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Perform basic filtering and search over entity listings.
 */
class EntityListManager extends AbstractEntityListManager
{
    /**
     * @var class-string<PersistableInterface>
     */
    protected string $entityName;
    protected ObjectManager $entityManager;
    protected ?Paginator $paginator = null;
    protected ?array $orderingArray = null;
    protected ?array $filteringArray = null;
    protected ?string $searchPattern = null;
    protected ?array $assignation = null;
    protected ?TranslationInterface $translation = null;

    /**
     * @param Request|null  $request
     * @param ObjectManager $entityManager
     * @param class-string<PersistableInterface> $entityName
     * @param array $preFilters
     * @param array $preOrdering
     */
    public function __construct(
        ?Request $request,
        ObjectManager $entityManager,
        string $entityName,
        array $preFilters = [],
        array $preOrdering = []
    ) {
        parent::__construct($request);
        $this->entityName = $entityName;
        $this->entityManager = $entityManager;
        $this->orderingArray = $preOrdering;
        $this->filteringArray = $preFilters;
        $this->assignation = [];
    }

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @param TranslationInterface|null $translation
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation = null)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Handle request to find filter to apply to entity listing.
     *
     * @param bool $disabled Disable pagination and filtering over GET params
     * @return void
     */
    public function handle(bool $disabled = false)
    {
        // transform the key chroot in parent
        if (array_key_exists('chroot', $this->filteringArray)) {
            if ($this->filteringArray["chroot"] instanceof Node) {
                /** @var NodeRepository $nodeRepo */
                $nodeRepo = $this->entityManager
                    ->getRepository(Node::class)
                    ->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes())
                    ->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
                $ids = $nodeRepo->findAllOffspringIdByNode($this->filteringArray["chroot"]); // get all offspringId
                if (array_key_exists('parent', $this->filteringArray)) {
                    // test if parent key exist
                    if (is_array($this->filteringArray["parent"])) {
                        // test if multiple parent id
                        if (
                            count(array_intersect($this->filteringArray["parent"], $ids))
                            != count($this->filteringArray["parent"])
                        ) {
                            // test if all parent are in the chroot
                            $this->filteringArray["parent"] = -1; // -1 for make the search return []
                        }
                    } else {
                        if ($this->filteringArray["parent"] instanceof Node) {
                            // make transform all id in int
                            $parent = $this->filteringArray["parent"]->getId();
                        } else {
                            $parent = (int) $this->filteringArray["parent"];
                        }
                        if (!in_array($parent, $ids, true)) {
                            $this->filteringArray["parent"] = -1;
                        }
                    }
                } else {
                    $this->filteringArray["parent"] = $ids;
                }
            }
            unset($this->filteringArray["chroot"]); // remove placeholder
        }

        if (false === $disabled && null !== $this->request) {
            if (
                $this->allowRequestSorting &&
                $this->request->query->get('field') &&
                $this->request->query->get('ordering')
            ) {
                $this->validateOrderingFieldName($this->request->query->get('field'));
                $this->orderingArray = [
                    $this->request->query->get('field') => $this->request->query->get('ordering')
                ];
                $this->queryArray['field'] = $this->request->query->get('field');
                $this->queryArray['ordering'] = $this->request->query->get('ordering');
            }

            if ($this->allowRequestSearching && $this->request->query->get('search') != "") {
                $this->searchPattern = $this->request->query->get('search');
                $this->queryArray['search'] = $this->request->query->get('search');
            }

            if (
                $this->request->query->has('item_per_page') &&
                $this->request->query->get('item_per_page') > 0
            ) {
                $this->setItemPerPage((int) $this->request->query->get('item_per_page'));
            }

            if (
                $this->request->query->has('page') &&
                $this->request->query->get('page') > 1
            ) {
                $this->setPage((int) $this->request->query->get('page'));
            } else {
                $this->setPage(1);
            }
        } else {
            /*
             * Disable pagination and paginator
             */
            $this->disablePagination();
        }

        $this->createPaginator();

        if (
            $this->allowRequestSearching &&
            false === $disabled &&
            null !== $this->request &&
            $this->request->query->get('search') != ""
        ) {
            $this->paginator->setSearchPattern($this->request->query->get('search'));
        }
    }

    protected function createPaginator(): void
    {
        if (
            $this->entityName === Node::class ||
            $this->entityName === 'RZ\Roadiz\CoreBundle\Entity\Node' ||
            $this->entityName === '\RZ\Roadiz\CoreBundle\Entity\Node' ||
            $this->entityName === "Node"
        ) {
            $this->paginator = new NodePaginator(
                $this->entityManager,
                $this->entityName,
                $this->itemPerPage,
                $this->filteringArray
            );
            $this->paginator->setTranslation($this->translation);
        } elseif (
            $this->entityName == NodesSources::class ||
            $this->entityName == 'RZ\Roadiz\CoreBundle\Entity\NodesSources' ||
            $this->entityName == '\RZ\Roadiz\CoreBundle\Entity\NodesSources' ||
            $this->entityName == "NodesSources" ||
            str_contains($this->entityName, NodeType::getGeneratedEntitiesNamespace())
        ) {
            $this->paginator = new NodesSourcesPaginator(
                $this->entityManager,
                $this->entityName,
                $this->itemPerPage,
                $this->filteringArray
            );
        } else {
            $this->paginator = new Paginator(
                $this->entityManager,
                $this->entityName,
                $this->itemPerPage,
                $this->filteringArray
            );
        }

        $this->paginator->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
        $this->paginator->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
    }

    /**
     * @return array
     */
    public function getAssignation(): array
    {
        return array_merge(parent::getAssignation(), [
            'search' => $this->searchPattern,
        ]);
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        if (
            $this->pagination === true &&
            null !== $this->paginator
        ) {
            return $this->paginator->getTotalCount();
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        if (
            $this->pagination === true &&
            null !== $this->paginator
        ) {
            return $this->paginator->getPageCount();
        }

        return 1;
    }

    /**
     * Return filtered entities.
     *
     * @return array|DoctrinePaginator
     */
    public function getEntities(): array|DoctrinePaginator
    {
        if ($this->pagination === true && null !== $this->paginator) {
            $this->paginator->setItemsPerPage($this->getItemPerPage());
            return $this->paginator->findByAtPage($this->orderingArray, $this->currentPage);
        } else {
            $repository = $this->entityManager->getRepository($this->entityName);
            if ($repository instanceof StatusAwareRepository) {
                $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
                $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
            }
            return $repository->findBy(
                $this->filteringArray,
                $this->orderingArray,
                $this->itemPerPage
            );
        }
    }
}
