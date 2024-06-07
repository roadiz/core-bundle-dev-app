<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;

/**
 * Perform basic filtering and search over entity listings.
 *
 * @extends EntityListManager<Tag>
 */
#[Exclude]
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
     * @return array
     */
    public function getEntities(): array
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
