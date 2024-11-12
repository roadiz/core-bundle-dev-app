<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;

/**
 * Perform basic filtering and search over entity listings.
 *
 * @template T of PersistableInterface
 */
#[Exclude]
class EntityListManager extends AbstractEntityListManager
{
    /**
     * @var Paginator<T>|null
     */
    protected ?Paginator $paginator = null;
    protected ?array $assignation = null;
    protected ?TranslationInterface $translation = null;

    /**
     * @param class-string<T> $entityName
     */
    public function __construct(
        ?Request $request,
        protected readonly ObjectManager $entityManager,
        protected readonly string $entityName,
        protected array $filteringArray = [],
        protected array $orderingArray = [],
    ) {
        parent::__construct($request);
        $this->assignation = [];
    }

    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @return $this
     */
    public function setTranslation(?TranslationInterface $translation = null): self
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Handle request to find filter to apply to entity listing.
     *
     * @param bool $disabled Disable pagination and filtering over GET params
     *
     * @throws \ReflectionException
     */
    public function handle(bool $disabled = false): void
    {
        // transform the key chroot in parent
        if (array_key_exists('chroot', $this->filteringArray)) {
            if ($this->filteringArray['chroot'] instanceof Node) {
                /** @var NodeRepository $nodeRepo */
                $nodeRepo = $this->entityManager
                    ->getRepository(Node::class)
                    ->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes())
                    ->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
                $ids = $nodeRepo->findAllOffspringIdByNode($this->filteringArray['chroot']); // get all offspringId
                if (array_key_exists('parent', $this->filteringArray)) {
                    // test if parent key exist
                    if (is_array($this->filteringArray['parent'])) {
                        // test if multiple parent id
                        if (
                            count(array_intersect($this->filteringArray['parent'], $ids))
                            != count($this->filteringArray['parent'])
                        ) {
                            // test if all parent are in the chroot
                            $this->filteringArray['parent'] = -1; // -1 for make the search return []
                        }
                    } else {
                        if ($this->filteringArray['parent'] instanceof Node) {
                            // make transform all id in int
                            $parent = $this->filteringArray['parent']->getId();
                        } else {
                            $parent = (int) $this->filteringArray['parent'];
                        }
                        if (!in_array($parent, $ids, true)) {
                            $this->filteringArray['parent'] = -1;
                        }
                    }
                } else {
                    $this->filteringArray['parent'] = $ids;
                }
            }
            unset($this->filteringArray['chroot']); // remove placeholder
        }

        $this->handleRequestQuery($disabled);
        $this->createPaginator();

        if (
            $this->allowRequestSearching
            && false === $disabled
            && null !== $this->request
        ) {
            $search = $this->request->query->get('search');
            if (\is_string($search) && '' !== $search) {
                $this->paginator->setSearchPattern($search);
            }
        }
    }

    protected function handleOrderingParam(string $field, string $ordering): void
    {
        $this->validateOrderingFieldName($field);
        $this->orderingArray = [
            $field => $ordering,
        ];
    }

    /**
     * @throws \ReflectionException
     */
    protected function createPaginator(): void
    {
        $reflectionClass = new \ReflectionClass($this->entityName);

        if (Node::class === $this->entityName) {
            // @phpstan-ignore-next-line
            $this->paginator = new NodePaginator(
                $this->entityManager,
                $this->entityName,
                $this->itemPerPage,
                $this->filteringArray
            );
            $this->paginator->setTranslation($this->translation);
        } elseif (
            NodesSources::class === $this->entityName
            || $reflectionClass->isSubclassOf(NodesSources::class)
        ) {
            // @phpstan-ignore-next-line
            $this->paginator = new NodesSourcesPaginator(
                $this->entityManager,
                // @phpstan-ignore-next-line
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

    public function getItemCount(): int
    {
        if (
            true === $this->pagination
            && null !== $this->paginator
        ) {
            return $this->paginator->getTotalCount();
        }

        return 0;
    }

    public function getPageCount(): int
    {
        if (
            true === $this->pagination
            && null !== $this->paginator
        ) {
            return $this->paginator->getPageCount();
        }

        return 1;
    }

    /**
     * Return filtered entities.
     *
     * @return array<T>
     */
    public function getEntities(): array
    {
        if (true === $this->pagination && null !== $this->paginator) {
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
