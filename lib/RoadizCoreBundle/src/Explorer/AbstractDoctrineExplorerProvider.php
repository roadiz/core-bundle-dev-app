<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManager;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractDoctrineExplorerProvider extends AbstractExplorerProvider
{
    public function __construct(
        protected ExplorerItemFactoryInterface $explorerItemFactory,
        protected ManagerRegistry $managerRegistry,
        protected RequestStack $requestStack,
        protected UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return class-string<PersistableInterface>
     */
    abstract protected function getProvidedClassname(): string;

    abstract protected function getDefaultCriteria(): array;

    abstract protected function getDefaultOrdering(): array;

    /**
     * @throws \ReflectionException
     */
    protected function doFetchItems(array $options = []): EntityListManagerInterface
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        /** @var EntityListManager<PersistableInterface> $listManager */
        $listManager = new EntityListManager(
            $this->requestStack->getCurrentRequest(),
            $this->managerRegistry->getManager(),
            $this->getProvidedClassname(),
            $this->getDefaultCriteria(),
            $this->getDefaultOrdering()
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage($this->options['itemPerPage']);
        $listManager->handle();
        $listManager->setPage((int) $this->options['page']);

        return $listManager;
    }

    #[\Override]
    public function getItems($options = []): array
    {
        $listManager = $this->doFetchItems($options);

        $items = [];
        foreach ($listManager->getEntities() as $entity) {
            $items[] = $this->toExplorerItem($entity);
        }

        return $items;
    }

    #[\Override]
    public function getFilters($options = []): array
    {
        $listManager = $this->doFetchItems($options);

        return $listManager->getAssignation();
    }

    #[\Override]
    public function getItemsById(array $ids = []): array
    {
        if (is_array($ids) && count($ids) > 0) {
            $entities = $this->managerRegistry->getRepository($this->getProvidedClassname())->findBy([
                'id' => $ids,
            ]);

            /*
             * Sort entities the same way IDs were given
             */
            usort($entities, fn ($a, $b) => array_search($a->getId(), $ids) <=> array_search($b->getId(), $ids));

            $items = [];
            foreach ($entities as $entity) {
                $items[] = $this->toExplorerItem($entity);
            }

            return $items;
        }

        return [];
    }

    #[\Override]
    public function toExplorerItem(mixed $item): ExplorerItemInterface
    {
        return $this->explorerItemFactory->createForEntity($item);
    }
}
