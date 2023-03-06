<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Perform basic filtering and search over entity listings.
 */
class TagListManager extends EntityListManager
{
    /**
     * @param Request|null  $request
     * @param ObjectManager $entityManager
     * @param array         $preFilters
     * @param array         $preOrdering
     */
    public function __construct(
        ?Request $request,
        ObjectManager $entityManager,
        array $preFilters = [],
        array $preOrdering = []
    ) {
        parent::__construct($request, $entityManager, Tag::class, $preFilters, $preOrdering);
    }

    /**
     * @return array|DoctrinePaginator
     */
    public function getEntities(): array|DoctrinePaginator
    {
        try {
            if ($this->searchPattern != '') {
                return $this->entityManager
                    ->getRepository(TagTranslation::class)
                    ->searchBy($this->searchPattern, $this->filteringArray, $this->orderingArray);
            } else {
                return $this->paginator->findByAtPage($this->filteringArray, $this->currentPage);
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}
